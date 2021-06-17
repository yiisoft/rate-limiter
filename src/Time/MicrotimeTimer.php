<?php

declare(strict_types=1);

namespace Yiisoft\Yii\RateLimiter\Time;

use const Yiisoft\Yii\RateLimiter\MILLISECONDS_PER_SECOND;

final class MicrotimeTimer implements TimerInterface
{
    public function nowInMilliseconds(): int
    {
        return (int)round(microtime(true) * MILLISECONDS_PER_SECOND);
    }
}
