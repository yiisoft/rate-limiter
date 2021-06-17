<?php

declare(strict_types=1);

namespace Yiisoft\Yii\RateLimiter\Tests;

use Yiisoft\Yii\RateLimiter\Time\TimerInterface;

final class FrozenTimeTimer implements TimerInterface
{
    private static ?int $mark = null;

    public static function setTimeMark(int $mark): void
    {
        self::$mark = $mark;
    }

    public function nowInMilliseconds(): int
    {
        return null !== self::$mark ? self::$mark : (int)round(microtime(true));
    }
}
