<?php

declare(strict_types=1);

namespace Yiisoft\Yii\RateLimiter\Tests\Fixtures;

use Yiisoft\Yii\RateLimiter\CounterInterface;
use Yiisoft\Yii\RateLimiter\CounterState;

final class FakeCounter implements CounterInterface
{
    private int $remaining;

    public function __construct(private int $limit, private int $reset)
    {
        $this->remaining = $limit;
    }

    public function hit(string $id): CounterState
    {
        $this->remaining--;
        return new CounterState($this->limit, $this->remaining, $this->reset);
    }
}
