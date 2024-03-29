<?php

declare(strict_types=1);

namespace Yiisoft\Yii\RateLimiter\Tests\Fixtures;

use Psr\SimpleCache\CacheInterface;
use Yiisoft\Yii\RateLimiter\Storage\StorageInterface;

final class FakeSimpleCacheStorage implements StorageInterface
{
    private const DEFAULT_DIRTY_READ_COUNT = 8;

    private ?float $dirtyReadValue = null;
    private int $remainingDirtyReadCount = 0;

    public function __construct(
        private CacheInterface $cache,
        private int $dirtyReadCount = self::DEFAULT_DIRTY_READ_COUNT,
    ) {
    }

    public function saveIfNotExists(string $key, mixed $value, int $ttl): bool
    {
        return $this->cache->set($key, $value, $ttl);
    }

    public function saveCompareAndSwap(string $key, mixed $oldValue, mixed $newValue, int $ttl): bool
    {
        return $this->cache->set($key, $newValue, $ttl);
    }

    public function get(string $key): ?float
    {
        // Simulate dirty reading scenarios in this SimpleCacheStorage class
        if ($this->remainingDirtyReadCount > 0 && $this->dirtyReadValue !== null) {
            $this->remainingDirtyReadCount--;
            return $this->dirtyReadValue;
        }

        $readValue = $this->cache->get($key, false);
        if ($readValue === false) {
            return null;
        }

        $readValue = (float)$readValue;
        $this->dirtyReadValue = $readValue;
        $this->remainingDirtyReadCount = $this->dirtyReadCount;

        return $readValue;
    }
}
