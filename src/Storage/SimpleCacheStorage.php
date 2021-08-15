<?php

declare(strict_types=1);

namespace Yiisoft\Yii\RateLimiter\Storage;

use Psr\SimpleCache\CacheInterface;

final class SimpleCacheStorage implements StorageInterface
{
    private CacheInterface $cache;

    public function __construct(CacheInterface $cache)
    {
        $this->cache = $cache;
    }

    public function save(string $key, $value, int $ttl): void
    {
        $this->cache->set($key, $value, $ttl);
    }

    public function get(string $key, $default = null)
    {
        return $this->cache->get($key, $default);
    }
}
