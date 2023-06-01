<?php

declare(strict_types=1);

namespace Yiisoft\Yii\RateLimiter\Tests;

use Yiisoft\Yii\RateLimiter\Time\TimerInterface;

/**
 * Timer that we set manually.
 */
final class FrozenTimeTimer implements TimerInterface
{
    private const MILLISECONDS_PER_SECOND = 1000;

    private static ?int $mark = null;

    /**
     * Set time.
     *
     * @param int $mark Time in milliseconds.
     */
    public static function setTimeMark(int $mark): void
    {
        self::$mark = $mark;
    }

    /**
     * Get current time.
     *
     * @return int Current time in milliseconds.
     */
    public function nowInMilliseconds(): int
    {
        return self::$mark ?? (int)round(microtime(true) * self::MILLISECONDS_PER_SECOND);
    }
}
