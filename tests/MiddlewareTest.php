<?php

declare(strict_types=1);

namespace Yiisoft\Yii\RateLimiter\Tests;

use Nyholm\Psr7\Factory\Psr17Factory;
use Nyholm\Psr7\Response;
use Nyholm\Psr7\ServerRequest;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Yiisoft\Cache\ArrayCache;
use Yiisoft\Http\Method;
use Yiisoft\Http\Status;
use Yiisoft\Yii\RateLimiter\Counter;
use Yiisoft\Yii\RateLimiter\CounterInterface;
use Yiisoft\Yii\RateLimiter\Policy\LimitAlways;
use Yiisoft\Yii\RateLimiter\Policy\LimitCallback;
use Yiisoft\Yii\RateLimiter\Policy\LimitPerIp;
use Yiisoft\Yii\RateLimiter\Policy\LimitPolicyInterface;
use Yiisoft\Yii\RateLimiter\LimitRequestsMiddleware;
use Yiisoft\Yii\RateLimiter\Storage\SimpleCacheStorage;

final class MiddlewareTest extends TestCase
{
    public function testSingleRequestWorksAsExpected(): void
    {
        $counter = new FakeCounter(100, 100);
        $response = $this
            ->createRateLimiter($counter)
            ->process($this->createRequest(), $this->createRequestHandler());
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

        $response
            ->getBody()
            ->rewind();
        $this->assertSame(Status::TEXTS[Status::TOO_MANY_REQUESTS], $response
            ->getBody()
            ->getContents());
    }

    public function testWithLimitingAll(): void
    {
        $counter = new Counter(new SimpleCacheStorage(new ArrayCache()), 2, 5);
        $middleware = $this->createRateLimiter($counter, new LimitAlways());

        // last allowed request
        $response = $middleware->process(
            $this->createRequest(Method::GET, '/', ['REMOTE_ADDR' => '193.186.62.12']),
            $this->createRequestHandler()
        );
        $this->assertEquals(200, $response->getStatusCode());

        $headers = $response->getHeaders();

        $this->assertEquals(['2'], $headers['X-Rate-Limit-Limit']);
        $this->assertEquals(['1'], $headers['X-Rate-Limit-Remaining']);

        // first denied request
        $response = $middleware->process(
            $this->createRequest(Method::GET, '/', ['REMOTE_ADDR' => '193.186.62.12']),
            $this->createRequestHandler()
        );

        $headers = $response->getHeaders();

        $this->assertEquals(429, $response->getStatusCode());
        $this->assertEquals(['2'], $headers['X-Rate-Limit-Limit']);
        $this->assertEquals(['0'], $headers['X-Rate-Limit-Remaining']);

        // second denied request for other user
        $response = $middleware->process(
            $this->createRequest(Method::GET, '/', ['REMOTE_ADDR' => '193.186.62.13']),
            $this->createRequestHandler()
        );

        $headers = $response->getHeaders();

        $this->assertEquals(429, $response->getStatusCode());
        $this->assertEquals(['2'], $headers['X-Rate-Limit-Limit']);
        $this->assertEquals(['0'], $headers['X-Rate-Limit-Remaining']);
    }

    public function testWithLimitingPerUser(): void
    {
        $counter = new Counter(new SimpleCacheStorage(new ArrayCache()), 2, 5);
        $middleware = $this->createRateLimiter($counter, new LimitPerIp());

        // last allowed request
        $response = $middleware->process(
            $this->createRequest(Method::GET, '/', ['REMOTE_ADDR' => '193.186.62.12']),
            $this->createRequestHandler()
        );
        $this->assertEquals(200, $response->getStatusCode());

        $headers = $response->getHeaders();

        $this->assertEquals(['2'], $headers['X-Rate-Limit-Limit']);
        $this->assertEquals(['1'], $headers['X-Rate-Limit-Remaining']);

        // first denied request
        $response = $middleware->process(
            $this->createRequest(Method::GET, '/', ['REMOTE_ADDR' => '193.186.62.12']),
            $this->createRequestHandler()
        );

        $headers = $response->getHeaders();

        $this->assertEquals(429, $response->getStatusCode());
        $this->assertEquals(['2'], $headers['X-Rate-Limit-Limit']);
        $this->assertEquals(['0'], $headers['X-Rate-Limit-Remaining']);

        // second not denied request for other user
        $response = $middleware->process(
            $this->createRequest(Method::GET, '/', ['REMOTE_ADDR' => '193.186.62.13']),
            $this->createRequestHandler()
        );

        $headers = $response->getHeaders();

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals(['2'], $headers['X-Rate-Limit-Limit']);
        $this->assertEquals(['1'], $headers['X-Rate-Limit-Remaining']);

        $response = $middleware->process(
            $this->createRequest(Method::GET, '/', ['REMOTE_ADDR' => '193.186.62.13']),
            $this->createRequestHandler()
        );

        $headers = $response->getHeaders();

        $this->assertEquals(429, $response->getStatusCode());
        $this->assertEquals(['2'], $headers['X-Rate-Limit-Limit']);
        $this->assertEquals(['0'], $headers['X-Rate-Limit-Remaining']);
    }

    public function testWithLimitingFunction(): void
    {
        // test unlimited requests for always unique id
        $counter = new Counter(new SimpleCacheStorage(new ArrayCache()), 2, 5);
        $middleware = $this->createRateLimiter(
            $counter,
            new LimitCallback(fn (ServerRequestInterface $_request): string => time() . uniqid() . uniqid())
        );

        for ($i = 0; $i < 10; $i++) {
            $response = $middleware->process($this->createRequest(), $this->createRequestHandler());

            $this->assertEquals(200, $response->getStatusCode());

            $headers = $response->getHeaders();

            $this->assertEquals(['2'], $headers['X-Rate-Limit-Limit']);
            $this->assertEquals(['1'], $headers['X-Rate-Limit-Remaining']);
        }

        // test limited requests for always same id
        $counter = new Counter(new SimpleCacheStorage(new ArrayCache()), 2, 5);
        $middleware = $this->createRateLimiter(
            $counter,
            new LimitCallback(fn (ServerRequestInterface $_request): string => 'id')
        );

        $response = $middleware->process($this->createRequest(), $this->createRequestHandler());

        $this->assertEquals(200, $response->getStatusCode());

        for ($i = 0; $i < 10; $i++) {
            $response = $middleware->process($this->createRequest(), $this->createRequestHandler());

            $this->assertEquals(429, $response->getStatusCode());

            $headers = $response->getHeaders();

            $this->assertEquals(['2'], $headers['X-Rate-Limit-Limit']);
            $this->assertEquals(['0'], $headers['X-Rate-Limit-Remaining']);
        }
    }

    /**
     * 
     * Testing fail to store updated the rate limit data after maximum attempts.
     * 
     */
    public function testWithExceedingMaxAttempts(): void
    {
        $timer = new FrozenTimeTimer();
        $dirtyReadCount = 2;
        $storage = new FakeApcuStorage($dirtyReadCount);
        $counter = new Counter(
            $storage,
            10,
            1,
            86400,
            'rate-limiter-',
            $timer,
            1
        );
        $middleware = $this->createRateLimiter($counter, new LimitAlways());
        $middleware->process(
            $this->createRequest(Method::GET, '/', ['REMOTE_ADDR' => '193.186.62.12']),
            $this->createRequestHandler()
        );
        $middleware->process(
            $this->createRequest(Method::GET, '/', ['REMOTE_ADDR' => '193.186.62.12']),
            $this->createRequestHandler()
        );

        $response = $middleware->process(
            $this->createRequest(Method::GET, '/', ['REMOTE_ADDR' => '193.186.62.12']),
            $this->createRequestHandler()
        );

        $this->assertEquals(Status::CONFLICT, $response->getStatusCode());
    }

    private function createRequestHandler(): RequestHandlerInterface
    {
        $requestHandler = $this->createMock(RequestHandlerInterface::class);
        $requestHandler
            ->method('handle')
            ->willReturn(new Response(200));

        return $requestHandler;
    }

    private function createRequest(
        string $method = Method::GET,
        string $uri = '/',
        array $params = []
    ): ServerRequestInterface {
        return new ServerRequest($method, $uri, [], null, '1.1', $params);
    }

    private function createRateLimiter(
        CounterInterface $counter,
        LimitPolicyInterface $limitingPolicy = null
    ): LimitRequestsMiddleware {
        return new LimitRequestsMiddleware($counter, new Psr17Factory(), $limitingPolicy);
    }
}
