<?php

declare(strict_types=1);

namespace Yiisoft\Yii\RateLimiter\Tests;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use Yiisoft\Cache\ArrayCache;
use Yiisoft\Yii\RateLimiter\Counter;
use Yiisoft\Yii\RateLimiter\Time\MicrotimeTimer;

final class CounterTest extends TestCase
{
    public function testStatisticsShouldBeCorrectWhenLimitIsNotReached(): void
    {
        $counter = new Counter(new SimpleCacheAdapter(new ArrayCache()), 2, 5);

        $statistics = $counter->hit('key');
        $this->assertEquals(2, $statistics->getLimit());
        $this->assertEquals(1, $statistics->getRemaining());
        $this->assertGreaterThanOrEqual(time(), $statistics->getResetTime());
        $this->assertFalse($statistics->isLimitReached());
    }

    public function testStatisticsShouldBeCorrectWhenLimitIsReached(): void
    {
        $counter = new Counter(new SimpleCacheAdapter(new ArrayCache()), 2, 4);

        $statistics = $counter->hit('key');
        $this->assertEquals(2, $statistics->getLimit());
        $this->assertEquals(1, $statistics->getRemaining());
        $this->assertGreaterThanOrEqual(time(), $statistics->getResetTime());
        $this->assertFalse($statistics->isLimitReached());

        $statistics = $counter->hit('key');
        $this->assertEquals(2, $statistics->getLimit());
        $this->assertEquals(0, $statistics->getRemaining());
        $this->assertGreaterThanOrEqual(time(), $statistics->getResetTime());
        $this->assertTrue($statistics->isLimitReached());
    }

    public function testShouldNotBeAbleToSetInvalidLimit(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new Counter(new SimpleCacheAdapter(new ArrayCache()), 0, 60);
    }

    public function testShouldNotBeAbleToSetInvalidPeriod(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new Counter(new SimpleCacheAdapter(new ArrayCache()), 10, 0);
    }

    public function testIncrementMustBeUniformAfterLimitIsReached(): void
    {
        if (PHP_OS_FAMILY === 'Windows') {
            $this->markTestSkipped('On Windows, the "usleep()" function used in this test may not work correctly.');
        }

        $counter = new Counter(new SimpleCacheAdapter(new ArrayCache()), 10, 1);

        for ($i = 0; $i < 10; $i++) {
            $counter->hit('key');
        }

        for ($i = 0; $i < 5; $i++) {
            usleep(110000); // period(microseconds) / limit + 10ms(cost work)
            $statistics = $counter->hit('key');
            $this->assertEquals(1, $statistics->getRemaining());
        }
    }

    public function testCustomTtl(): void
    {
        $cache = new SimpleCacheAdapter(new ArrayCache());

        $counter = new Counter(
            $cache,
            1,
            1,
            1,
            new FrozenTimeTimer()
        );

        $counter->hit('test');

        FrozenTimeTimer::setTimeMark((new MicrotimeTimer())->nowInMilliseconds() + 2);

        self::assertNull($cache->get('rate-limiter-test'));
    }
}
