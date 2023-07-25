<?php

declare(strict_types=1);

namespace Yiisoft\Yii\RateLimiter\Time;

interface TimerInterface
{
    public function nowInMilliseconds(): float;
}
