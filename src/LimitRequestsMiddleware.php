<?php

declare(strict_types=1);

namespace Yiisoft\Yii\RateLimiter;

use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Yiisoft\Http\Status;
use Yiisoft\Yii\RateLimiter\Policy\LimitPerIp;
use Yiisoft\Yii\RateLimiter\Policy\LimitPolicyInterface;

/**
 * RateLimiter helps to prevent abuse by limiting the number of requests that could be me made consequentially.
 *
 * For example, you may want to limit the API usage of each user to be at most 100 API calls within a period of 10
 * minutes. If too many requests are received from a user within the stated period of the time, a response with status
 * code 429 (meaning "Too Many Requests") should be returned.
 *
 * @psalm-type CounterIdCallback = callable(ServerRequestInterface):string
 */
final class LimitRequestsMiddleware implements MiddlewareInterface
{
    private CounterInterface $counter;

    private ResponseFactoryInterface $responseFactory;

    private LimitPolicyInterface $limitingPolicy;

    public function __construct(
        CounterInterface $counter,
        ResponseFactoryInterface $responseFactory,
        ?LimitPolicyInterface $limitingPolicy = null
    ) {
        $this->counter = $counter;
        $this->responseFactory = $responseFactory;
        $this->limitingPolicy = $limitingPolicy ?: new LimitPerIp();
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $state = $this->counter->hit($this->limitingPolicy->fingerprint($request));

        if ($state->isLimitReached()) {
            $response = $this->createErrorResponse();
        } else {
            $response = $handler->handle($request);
        }

        return $this->addHeaders($response, $state);
    }

    private function createErrorResponse(): ResponseInterface
    {
        $response = $this->responseFactory->createResponse(Status::TOO_MANY_REQUESTS);
        $response->getBody()->write(Status::TEXTS[Status::TOO_MANY_REQUESTS]);

        return $response;
    }

    private function addHeaders(ResponseInterface $response, CounterState $result): ResponseInterface
    {
        return $response
            ->withHeader('X-Rate-Limit-Limit', (string)$result->getLimit())
            ->withHeader('X-Rate-Limit-Remaining', (string)$result->getRemaining())
            ->withHeader('X-Rate-Limit-Reset', (string)$result->getResetTime());
    }
}
