<?php

declare(strict_types=1);

namespace Yiisoft\Yii\RateLimiter;

interface CounterInterface
{
    /**
     * @param string $id Set counter ID.
     * Counters with non-equal IDs are counted separately.
     *
     * @return CounterState
     */
    public function hit(string $id): CounterState;
}
