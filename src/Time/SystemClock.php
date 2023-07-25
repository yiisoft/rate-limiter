<?php

declare(strict_types=1);

namespace Yiisoft\Yii\RateLimiter\Time;

use DateTimeImmutable;
use Psr\Clock\ClockInterface;

final class SystemClock implements ClockInterface
{
    public function now(): DateTimeImmutable
    {
        return new DateTimeImmutable();
    }
}
