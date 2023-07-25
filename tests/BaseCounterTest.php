<?php

declare(strict_types=1);

namespace Yiisoft\Yii\RateLimiter\Tests;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use Yiisoft\Yii\RateLimiter\Counter;
use Yiisoft\Yii\RateLimiter\Storage\StorageInterface;
use Yiisoft\Yii\RateLimiter\Tests\Fixtures\FrozenClock;
use Yiisoft\Yii\RateLimiter\Tests\Support\Assert;

abstract class BaseCounterTest extends TestCase
{
    abstract protected function getStorage(): StorageInterface;

    public function testStatisticsShouldBeCorrectWhenLimitIsNotReached(): void
    {
        $counter = new Counter($this->getStorage(), 2, 5);

        $statistics = $counter->hit('key');
        $this->assertEquals(2, $statistics->getLimit());
        $this->assertEquals(1, $statistics->getRemaining());
        $this->assertGreaterThanOrEqual(time(), $statistics->getResetTime());
        $this->assertFalse($statistics->isLimitReached());
    }

    public function testStatisticsShouldBeCorrectWhenLimitIsReached(): void
    {
        $counter = new Counter($this->getStorage(), 2, 4);

        $counter->hit('key');
        $statistics = $counter->hit('key');

        $this->assertEquals(2, $statistics->getLimit());
        $this->assertEquals(0, $statistics->getRemaining());
        $this->assertGreaterThanOrEqual(time(), $statistics->getResetTime());
        $this->assertTrue($statistics->isLimitReached());
    }

    public function testShouldNotBeAbleToSetInvalidLimit(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new Counter($this->getStorage(), 0, 60);
    }

    public function testShouldNotBeAbleToSetInvalidPeriod(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new Counter($this->getStorage(), 10, 0);
    }

    public function testIncrementMustBeUniformAfterLimitIsReached(): void
    {
        $timer = new FrozenClock();

        $counter = new Counter(
            $this->getStorage(),
            10,
            1,
            86400,
            'rate-limiter-',
            $timer
        );

        // Start with the limit reached.
        for ($i = 0; $i < 10; $i++) {
            $counter->hit('key');
        }

        for ($i = 0; $i < 5; $i++) {
            // Move timer forward for (period in milliseconds / limit)
            // i.e. once in period / limit remaining allowance should be increased by 1.
            $timer->modify('+100 milliseconds');
            $statistics = $counter->hit('key');
            $this->assertEquals(1, $statistics->getRemaining());
        }
    }

    public function testCustomTtl(): void
    {
        $storage = $this->getStorage();

        $clock = new FrozenClock();
        $counter = new Counter(
            $storage,
            1,
            1,
            1,
            'rate-limiter-',
            $clock
        );

        $counter->hit('test');

        $clock->modify('+2 milliseconds');

        self::assertNull($storage->get('rate-limiter-test'));
    }

    public function testGetKey(): void
    {
        $counter = new Counter($this->getStorage(), 1, 1, 1, 'rate-limiter-');

        $this->assertSame('rate-limiter-key', Assert::invokeMethod($counter, 'getStorageKey', ['key']));
    }
}
