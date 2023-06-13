<?php

declare(strict_types=1);

namespace Yiisoft\Yii\RateLimiter\Tests;

use Psr\SimpleCache\CacheInterface;
use Yiisoft\Yii\RateLimiter\Storage\StorageInterface;

final class FakeSimpleCacheStorage implements StorageInterface
{
    private const DEFAULT_DIRTY_READ_COUNT = 8;

    private int $dirty_read_value = 0;
    private int $remaining_dirty_read_count = 0;
    public function __construct(
        private CacheInterface $cache,
        private int $dirty_read_count = self::DEFAULT_DIRTY_READ_COUNT,
    )
    {
    }

    public function saveIfNotExists(string $key, int $value, int $ttl): bool
    {
        return $this->cache->set($key, $value, $ttl);
    }

    public function saveCompareAndSwap(string $key, int $oldValue, int $newValue, int $ttl): bool
    {
        return $this->cache->set($key, $newValue, $ttl);
    }

    public function get(string $key): mixed
    {
        if ($this->remaining_dirty_read_count > 0 && $this->dirty_read_value != 0) {
            $this->remaining_dirty_read_count--;
            return $this->dirty_read_value;
        }

        $read_value = $this->cache->get($key);
        if ($read_value) {
            $this->dirty_read_value = $read_value;
            $this->remaining_dirty_read_count = $this->dirty_read_count;
        }

        return $read_value;
    }
}
