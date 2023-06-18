<?php

declare(strict_types=1);

namespace Yiisoft\Yii\RateLimiter\Tests;

use Yiisoft\Yii\RateLimiter\Counter;
use Yiisoft\Yii\RateLimiter\Storage\SimpleCacheStorage;
use Yiisoft\Yii\RateLimiter\Storage\StorageInterface;
use Yiisoft\Cache\ArrayCache;

final class CounterTest extends BaseCounterTest
{
    protected function createStorage(): StorageInterface
    {
        return new SimpleCacheStorage(new ArrayCache());
    }

    /**
     * 
     * Testing that in concurrent scenarios, when dirty reads occur,
     * the current limiter cannot be as expected By 'SimpleCacheStorage'.
     * 
     */
    public function testConcurrentHitsWithDirtyReading(): void
    {
        $timer = new FrozenTimeTimer();
        $storage = new FakeSimpleCacheStorage(new ArrayCache(), 5);
        $limit_hits = 10;
        $counter = new Counter(
            $storage,
            $limit_hits,
            1,
            86400,
            'rate-limiter-',
            $timer
        );

        $total_hits = 0;
        do {
            ++$total_hits;

            $statistics = $counter->hit('key');

            $remaining = $statistics->getRemaining();
            if ($remaining <= 0) {
                break;
            }
        } while(true);

        $this->assertGreaterThan($limit_hits, $total_hits);
    }
}
