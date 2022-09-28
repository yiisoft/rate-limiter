<?php

declare(strict_types=1);

namespace Yiisoft\Yii\RateLimiter\Storage;

/**
 * Counter storage.
 */
interface StorageInterface
{
    /**
     * Persists counter value in the storage, uniquely referenced by a key with an optional expiration TTL time.
     *
     * @param string $key The ID of the counter to store.
     * @param mixed $value The value of the counter.
     * @param int $ttl The TTL value of this counter.
     */
    public function save(string $key, $value, int $ttl): void;

    /**
     * Fetches a counter value from the storage.
     *
     * @param string $key The unique key of this counter in the storage.
     * @param mixed $default Default value to return if the counter does not exist.
     *
     * @return mixed The value of the counter from the storage, or $default in case of no counter with such key present.
     */
    public function get(string $key, $default = null);
}
