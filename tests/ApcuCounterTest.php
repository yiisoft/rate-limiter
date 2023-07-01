<?php

declare(strict_types=1);

namespace Yiisoft\Yii\RateLimiter\Tests;

use Yiisoft\Yii\RateLimiter\Counter;
use Yiisoft\Yii\RateLimiter\Exception\OutOfMaxAttemptsException;
use Yiisoft\Yii\RateLimiter\Storage\ApcuStorage;
use Yiisoft\Yii\RateLimiter\Storage\StorageInterface;
use Yiisoft\Yii\RateLimiter\Tests\FakeApcuStorage;

final class ApcuCounterTest extends BaseCounterTest
{
    protected function createStorage(): StorageInterface
    {
        return new ApcuStorage();
    }

    protected function clearStorage(): bool
    {
        return apcu_clear_cache();
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->clearStorage();
    }

    /**
     * 
     * Testing that in concurrent scenarios, when dirty reads occur,
     * the current limiter still performs as expected By 'ApcuStorage'.
     * 
     */
    public function testConcurrentHitsWithDirtyReading(): void
    {
        $timer = new FrozenTimeTimer();
        $storage = new FakeApcuStorage(5);
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
            if ($remaining <= 0) {
                break;
            }
        } while(true);

        $this->assertEquals($limitHits, $totalHits);
    }

    public function testOutOfMaxAttemptsException(): void
    {
        $timer = new FrozenTimeTimer();
        $storage = new FakeApcuStorage(2);
        $limitHits = 10;
        $counter = new Counter(
            $storage,
            $limitHits,
            1,
            86400,
            'rate-limiter-',
            $timer,
            1
        );

        $counter->hit('key');
        $counter->hit('key');

        $this->expectException(OutOfMaxAttemptsException::class);
        $counter->hit('key');
    }
}
