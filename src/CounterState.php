<?php

declare(strict_types=1);

namespace Yiisoft\Yii\RateLimiter;

final class CounterState
{
    private int $limit;
    private int $remaining;
    private int $resetTime;

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
    }

    /**
     * @return int The maximum number of requests allowed with a time period.
     */
    public function getLimit(): int
    {
        return $this->limit;
    }

    /**
     * @return int The number of remaining requests in the current time period.
     */
    public function getRemaining(): int
    {
        return $this->remaining;
    }

    /**
     * @return int Timestamp to wait until the rate limit resets.
     */
    public function getResetTime(): int
    {
        return $this->resetTime;
    }

    /**
     * @return bool If requests limit is reached.
     */
    public function isLimitReached(): bool
    {
        return 0 === $this->remaining;
    }
}
