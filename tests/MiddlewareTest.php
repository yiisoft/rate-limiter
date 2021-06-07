<?php

declare(strict_types=1);

namespace Yiisoft\Yii\RateLimiter\Tests;

use Nyholm\Psr7\Factory\Psr17Factory;
use Nyholm\Psr7\Response;
use Nyholm\Psr7\ServerRequest;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Yiisoft\Http\Method;
use Yiisoft\Http\Status;
use Yiisoft\Yii\RateLimiter\CounterInterface;
use Yiisoft\Yii\RateLimiter\Middleware;

final class MiddlewareTest extends TestCase
{
    public function testSingleRequestWorksAsExpected(): void
    {
        $counter = new FakeCounter(100, 100);
        $response = $this->createRateLimiter($counter)->process($this->createRequest(), $this->createRequestHandler());
        $this->assertEquals(200, $response->getStatusCode());

        $this->assertEquals(
            [
                'X-Rate-Limit-Limit' => ['100'],
                'X-Rate-Limit-Remaining' => ['99'],
                'X-Rate-Limit-Reset' => ['100'],
            ],
            $response->getHeaders()
        );
    }

    public function testLimitingIsStartedWhenExpected(): void
    {
        $counter = new FakeCounter(2, 100);
        $middleware = $this->createRateLimiter($counter);

        // last allowed request
        $response = $middleware->process($this->createRequest(), $this->createRequestHandler());
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals(
            [
                'X-Rate-Limit-Limit' => ['2'],
                'X-Rate-Limit-Remaining' => ['1'],
                'X-Rate-Limit-Reset' => ['100'],
            ],
            $response->getHeaders()
        );

        // first denied request
        $response = $middleware->process($this->createRequest(), $this->createRequestHandler());
        $this->assertEquals(429, $response->getStatusCode());
        $this->assertEquals(
            [
                'X-Rate-Limit-Limit' => ['2'],
                'X-Rate-Limit-Remaining' => ['0'],
                'X-Rate-Limit-Reset' => ['100'],
            ],
            $response->getHeaders()
        );

        $response->getBody()->rewind();
        $this->assertSame(Status::TEXTS[Status::TOO_MANY_REQUESTS], $response->getBody()->getContents());
    }

    public function testCounterIdCouldBeSet(): void
    {
        $counter = new FakeCounter(100, 100);
        $middleware = $this->createRateLimiter($counter)->withCounterId('custom-id');
        $middleware->process($this->createRequest(), $this->createRequestHandler());
        $this->assertEquals('custom-id', $counter->getId());
    }

    public function testCounterIdCouldBeSetWithCallback(): void
    {
        $counter = new FakeCounter(100, 100);
        $middleware = $this->createRateLimiter($counter)->withCounterIdCallback(
            static function (ServerRequestInterface $request) {
                return $request->getMethod();
            }
        );

        $middleware->process($this->createRequest(), $this->createRequestHandler());
        $this->assertEquals('GET', $counter->getId());
    }

    public function testGenerateId(): void
    {
        $counter = new FakeCounter(100, 100);
        $middleware = $this->createRateLimiter($counter);

        $middleware->process(
            $this->createRequest(Method::POST, '/HELLO-world/'),
            $this->createRequestHandler(),
        );

        $this->assertSame('post-/hello-world/', $counter->getId());
    }

    public function testImmutability(): void
    {
        $middleware = $this->createRateLimiter(new FakeCounter(100, 100));

        $this->assertNotSame($middleware, $middleware->withCounterId('x42'));
        $this->assertNotSame(
            $middleware,
            $middleware->withCounterIdCallback(static fn(ServerRequestInterface $request) => 'x42')
        );
    }

    private function createRequestHandler(): RequestHandlerInterface
    {
        $requestHandler = $this->createMock(RequestHandlerInterface::class);
        $requestHandler
            ->method('handle')
            ->willReturn(new Response(200));

        return $requestHandler;
    }

    private function createRequest(string $method = Method::GET, string $uri = '/'): ServerRequestInterface
    {
        return new ServerRequest($method, $uri);
    }

    private function createRateLimiter(CounterInterface $counter): Middleware
    {
        return new Middleware($counter, new Psr17Factory());
    }
}
