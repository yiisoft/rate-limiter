<?php

declare(strict_types=1);

namespace Yiisoft\Yii\RateLimiter\Storage;

use Psr\SimpleCache\CacheInterface;

use function is_float;
use function is_int;

final class SimpleCacheStorage implements StorageInterface
{
    public function __construct(private CacheInterface $cache)
    {
    }

    public function saveIfNotExists(string $key, float $value, int $ttl): bool
    {
        return $this->cache->set($key, $value, $ttl);
    }

    public function saveCompareAndSwap(string $key, float $oldValue, float $newValue, int $ttl): bool
    {
        return $this->cache->set($key, $newValue, $ttl);
    }

    public function get(string $key): ?float
    {
        /** @psalm-suppress MixedAssignment */
        $value = $this->cache->get($key);

        return (is_int($value) || is_float($value)) ? (float) $value : null;
    }
}
