<?php

declare(strict_types=1);

namespace Yiisoft\Yii\RateLimiter\Tests\Fixtures;

use DateTimeImmutable;
use Psr\Clock\ClockInterface;

/**
 * Frozen timer returns the same value for all calls.
 */
final class FrozenClock implements ClockInterface
{

    private DateTimeImmutable $now;
    public function __construct()
    {
        $this->now = new DateTimeImmutable();
    }
    public function now(): DateTimeImmutable
    {
        return $this->now;
    }

    public function modify(string $modifier): void
    {
        $this->now = $this->now->modify($modifier);
    }
}
