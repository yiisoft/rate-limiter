<?php

declare(strict_types=1);

namespace Yiisoft\Yii\RateLimiter;

/**
 * Rate limiter counter state stores information about when the next request won't be limited.
 */
final class CounterState
{
    /**
     * @param int $limit The maximum number of requests allowed with a time period.
     * @param int $remaining The number of remaining requests in the current time period.
     * @param int $resetTime Timestamp to wait until the rate limit resets.
     * @param bool $isExceedingMaxAttempts If fail to store updated the rate limit data after maximum attempts.
     */
    public function __construct(
        private int $limit, 
        private int $remaining, 
        private int $resetTime,
        private bool $isExceedingMaxAttempts = false
    ) {
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
        return $this->remaining === 0;
    }

    /**
     * @return bool If fail to store updated the rate limit data after maximum attempts.
     */
    public function isExceedingMaxAttempts(): bool
    {
        return $this->isExceedingMaxAttempts;
    }
}
