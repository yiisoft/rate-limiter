<?php

declare(strict_types=1);

namespace Yiisoft\Yii\RateLimiter\Tests;

use Yiisoft\Yii\RateLimiter\Exception\CannotUseException;
use Yiisoft\Yii\RateLimiter\Storage\StorageInterface;

final class FakeApcuStorage implements StorageInterface
{
    private const DEFAULT_DIRTY_READ_COUNT = 8;

    private int $dirtyReadValue = 0;
    private int $remainingDirtyReadCount = 0;
    public function __construct(
        private int $dirtyReadCount = self::DEFAULT_DIRTY_READ_COUNT,
    )
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
        if ($this->remainingDirtyReadCount > 0 && $this->dirtyReadValue != 0) {
            $this->remainingDirtyReadCount--;
            return $this->dirtyReadValue;
        }

        $readValue = apcu_fetch($key);
        if ($readValue) {
            $this->dirtyReadValue = $readValue;
            $this->remainingDirtyReadCount = $this->dirtyReadCount;
        }

        return $readValue;
    }
}
