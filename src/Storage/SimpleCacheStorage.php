<?php

declare(strict_types=1);

namespace Yiisoft\Yii\RateLimiter\Storage;

use InvalidArgumentException;
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

    public function get(string $key): ?float
    {
        $value = $this->cache->get($key);
        if (!is_int($value) && !is_float($value) && $value !== false && $value !== null) {
            throw new InvalidArgumentException('The value is not supported by SimpleCacheStorage, it must be int, float or null.');
        }

        $value = ($value === false || $value === null) ? null : (float)$value;
        return $value;
    }
}
