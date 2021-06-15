<?php

declare(strict_types=1);

namespace Yiisoft\Yii\RateLimiter;

/**
 * @psalm-immutable
 */
final class CounterState
{
    public int $limit;
    public int $remaining;
    public int $resetTime;
    public bool $isLimitReached;

    /**
     * @param int $limit The maximum number of requests allowed with a time period.
     * @param int $remaining The number of remaining requests in the current time period.
     * @param int $resetTime Timestamp to wait until the rate limit resets.
     */
    public function __construct(int $limit, int $remaining, int $resetTime)
    {
        $this->limit = $limit;
        $this->remaining = $remaining;
        $this->resetTime = $resetTime;
        $this->isLimitReached = 0 === $remaining;
    }
}
