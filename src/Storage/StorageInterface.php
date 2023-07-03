<?php

declare(strict_types=1);

namespace Yiisoft\Yii\RateLimiter\Storage;

/**
 * Counter storage.
 */
interface StorageInterface
{
    /**
     * Saves the value of key only if it was not already saved in the store.
     * It returns `false` if a value was saved. Otherwise, it returns `true`
     * and saves the value.
     * If the storage supports expiring keys, the key will expire after the provided TTL.
     *
     * @param string $key The ID of the counter to store.
     * @param float $value The value of the counter.
     * @param integer $ttl The TTL value of this counter.
     * @return boolean
     */
    public function saveIfNotExists(string $key, float $value, int $ttl): bool;

    /**
     * Compares the old value of key to the value which was saved in the store. 
     * If it matches, method saves it to the new value and returns `true`.
     * Otherwise, it returns `false`.
     * If the key does not exist in the storage, it returns `false`.
     * If the storage supports expiring keys, the key will expire after the provided TTL.
     *
     * @param string $key The ID of the counter to store.
     * @param float $oldValue The old value of the counter.
     * @param float $newValue The new value of the counter.
     * @param integer $ttl The TTL value of this counter.
     * @return boolean
     */
    public function saveCompareAndSwap(string $key, float $oldValue, float $newValue, int $ttl): bool;

    /**
     * Fetches a counter value from the storage.
     *
     * @param string $key The unique key of this counter in the storage.
     *
     * @return ?float It returns `float` if a value was saved. Otherwise, it returns `null`
     */
    public function get(string $key): ?float;
}
