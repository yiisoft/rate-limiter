<?php

declare(strict_types=1);

namespace Yiisoft\Yii\RateLimiter;

use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Yiisoft\Http\Status;

/**
 * RateLimiter helps to prevent abuse by limiting the number of requests that could be me made consequentially.
 *
 * For example, you may want to limit the API usage of each user to be at most 100 API calls within a period of 10
 * minutes. If too many requests are received from a user within the stated period of the time, a response with status
 * code 429 (meaning "Too Many Requests") should be returned.
 *
 * @psalm-type CounterIdCallback = callable(ServerRequestInterface):string
 */
final class Middleware implements MiddlewareInterface
{
    private CounterInterface $counter;

    private ResponseFactoryInterface $responseFactory;

    private ?string $counterId = null;

    /**
     * @var callable|null
     * @psalm-var CounterIdCallback|null
     */
    private $counterIdCallback;

    public function __construct(CounterInterface $counter, ResponseFactoryInterface $responseFactory)
    {
        $this->counter = $counter;
        $this->responseFactory = $responseFactory;
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $this->counter->setId($this->generateId($request));
        $result = $this->counter->incrementAndGetState();

        if ($result->isLimitReached()) {
            $response = $this->createErrorResponse();
        } else {
            $response = $handler->handle($request);
        }

        return $this->addHeaders($response, $result);
    }

    /**
     * @param callable|null $callback Callback to use for generating counter ID. Counters with non-equal IDs
     * are counted separately.
     *
     * @psalm-param CounterIdCallback $callback
     */
    public function withCounterIdCallback(?callable $callback): self
    {
        $new = clone $this;
        $new->counterIdCallback = $callback;

        return $new;
    }

    /**
     * @param string $id Counter ID. Counters with non-equal IDs are counted separately.
     */
    public function withCounterId(string $id): self
    {
        $new = clone $this;
        $new->counterId = $id;

        return $new;
    }

    private function createErrorResponse(): ResponseInterface
    {
        $response = $this->responseFactory->createResponse(Status::TOO_MANY_REQUESTS);
        $response->getBody()->write(Status::TEXTS[Status::TOO_MANY_REQUESTS]);

        return $response;
    }

    private function generateId(ServerRequestInterface $request): string
    {
        if ($this->counterIdCallback !== null) {
            return ($this->counterIdCallback)($request);
        }

        return $this->counterId ?? $this->generateIdFromRequest($request);
    }

    private function generateIdFromRequest(ServerRequestInterface $request): string
    {
        return strtolower($request->getMethod() . '-' . $request->getUri()->getPath());
    }

    private function addHeaders(ResponseInterface $response, CounterState $result): ResponseInterface
    {
        return $response
            ->withHeader('X-Rate-Limit-Limit', (string)$result->getLimit())
            ->withHeader('X-Rate-Limit-Remaining', (string)$result->getRemaining())
            ->withHeader('X-Rate-Limit-Reset', (string)$result->getResetTime());
    }
}
