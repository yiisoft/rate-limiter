<?php

declare(strict_types=1);

namespace Yiisoft\Yii\RateLimiter\Time;

final class MicrotimeTimer implements TimerInterface
{
    private const MILLISECONDS_PER_SECOND = 1000;

    public function nowInMilliseconds(): int
    {
        return (int) round(microtime(true) * self::MILLISECONDS_PER_SECOND);
    }
}
