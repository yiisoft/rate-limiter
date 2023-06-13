<?php

declare(strict_types=1);

namespace Yiisoft\Yii\RateLimiter\Storage;

use Yiisoft\Yii\RateLimiter\Exception\CannotUseException;

final class ApcuStorage implements StorageInterface
{
    public function __construct()
    {
        if (!extension_loaded('apcu') || ini_get('apc.enabled') === '0') {
            throw new CannotUseException('APCu extension is not loaded or not enabled.');
        }
    }

    public function saveIfNotExists(string $key, int $value, int $ttl): bool
    {
        return (bool)apcu_add($key, $value, $ttl);
    }

    public function saveCompareAndSwap(string $key, int $oldValue, int $newValue, int $ttl): bool
    {
        return  (bool)apcu_cas($key, $oldValue, $newValue);
    }

    public function get(string $key): mixed
    {
        return apcu_fetch($key);
    }
}
