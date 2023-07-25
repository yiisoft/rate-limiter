<?php

declare(strict_types=1);

namespace Yiisoft\Yii\RateLimiter\Tests\Fixtures;

use Yiisoft\Yii\RateLimiter\Time\TimerInterface;

/**
 * Timer that we set manually.
 */
final class FrozenTimeTimer implements TimerInterface
{
    private const MILLISECONDS_PER_SECOND = 1000;

    private static ?float $mark = null;

    /**
     * Set time.
     *
     * @param float $mark Time in milliseconds.
     */
    public static function setTimeMark(float $mark): void
    {
        self::$mark = $mark;
    }

    /**
     * Get current time.
     *
     * @return float Current time in milliseconds.
     */
    public function nowInMilliseconds(): float
    {
        return self::$mark ?? round(microtime(true) * self::MILLISECONDS_PER_SECOND);
    }
}
