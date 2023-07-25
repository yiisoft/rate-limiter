<?php

declare(strict_types=1);

namespace Yiisoft\Yii\RateLimiter\Tests;

use Yiisoft\Cache\ArrayCache;
use Yiisoft\Yii\RateLimiter\Counter;
use Yiisoft\Yii\RateLimiter\Storage\SimpleCacheStorage;
use Yiisoft\Yii\RateLimiter\Storage\StorageInterface;
use Yiisoft\Yii\RateLimiter\Tests\Fixtures\FakeSimpleCacheStorage;
use Yiisoft\Yii\RateLimiter\Tests\Fixtures\FrozenClock;

final class CounterTest extends BaseCounterTest
{
    protected function getStorage(): StorageInterface
    {
        return new SimpleCacheStorage(new ArrayCache());
    }

    /**
     * Testing that in concurrent scenarios, when dirty reads occur,
     * the current limiter can't be as expected By 'SimpleCacheStorage'.
     */
    public function testConcurrentHitsWithDirtyReading(): void
    {
        $timer = new FrozenClock();
        $storage = new FakeSimpleCacheStorage(new ArrayCache(), 5);
        $limitHits = 10;
        $counter = new Counter(
            $storage,
            $limitHits,
            1,
            86400,
            'rate-limiter-',
            $timer
        );

        $totalHits = 0;
        do {
            ++$totalHits;

            $statistics = $counter->hit('key');

            $remaining = $statistics->getRemaining();
            if ($remaining === 0) {
                break;
            }
        } while (true);

        $this->assertGreaterThan($limitHits, $totalHits);
    }
}
