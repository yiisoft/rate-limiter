<?php

declare(strict_types=1);

namespace Yiisoft\Yii\RateLimiter;

/**
 * CounterInterface implementations describe the limiting algorithm. On each {@see CounterInterface::hit()} call
 * it determines when the next hit won't be limited.
 */
interface CounterInterface
{
    /**
     * Determines when the next hit won't be limited. Should be called on each request.
     *
     * @param string $id Counter ID. Counters with distinct IDs do not affect each other.
     * For example, using a current user ID will limit for the current user and using IP will limit by IP.
     *
     * @return CounterState Information about when the next hit won't be limited.
     */
    public function hit(string $id): CounterState;
}
