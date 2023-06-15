<?php

declare(strict_types=1);

namespace Yiisoft\Yii\RateLimiter\Storage;

use Psr\SimpleCache\CacheInterface;

final class SimpleCacheStorage implements StorageInterface
{
    public function __construct(private CacheInterface $cache)
    {
    }

    public function saveIfNotExists(string $key, mixed $value, int $ttl): bool
    {
        return $this->cache->set($key, $value, $ttl);
    }

    public function saveCompareAndSwap(string $key, mixed $oldValue, mixed $newValue, int $ttl): bool
    {
        return $this->cache->set($key, $newValue, $ttl);
    }

    public function get(string $key): mixed
    {
        return $this->cache->get($key);
    }
}
