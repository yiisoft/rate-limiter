<?php

declare(strict_types=1);

namespace Yiisoft\Yii\RateLimiter\Tests;

use Yiisoft\Yii\RateLimiter\CounterInterface;
use Yiisoft\Yii\RateLimiter\CounterState;

final class FakeCounter implements CounterInterface
{
    private int $remaining;
    private int $limit;
    private int $reset;

    public function __construct(int $limit, int $reset)
    {
        $this->reset = $reset;
        $this->limit = $limit;
        $this->remaining = $limit;
    }

    public function hit(string $id): CounterState
    {
        $this->remaining--;
        return new CounterState($this->limit, $this->remaining, $this->reset);
    }
}
