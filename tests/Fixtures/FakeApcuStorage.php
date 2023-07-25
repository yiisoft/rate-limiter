<?php

declare(strict_types=1);

namespace Yiisoft\Yii\RateLimiter\Tests\Fixtures;

use Yiisoft\Yii\RateLimiter\Storage\StorageInterface;

final class FakeApcuStorage implements StorageInterface
{
    private const DEFAULT_FIX_PRECISION_RATE = 1000;
    private const DEFAULT_DIRTY_READ_COUNT = 8;

    private ?float $dirtyReadValue = null;
    private int $remainingDirtyReadCount = 0;

    public function __construct(
        private int $dirtyReadCount = self::DEFAULT_DIRTY_READ_COUNT,
        private int $fixPrecisionRate = self::DEFAULT_FIX_PRECISION_RATE,
    ) {
    }

    public function saveIfNotExists(string $key, float $value, int $ttl): bool
    {
        $value = (int) ($value * $this->fixPrecisionRate);
        return (bool)apcu_add($key, $value, $ttl);
    }

    public function saveCompareAndSwap(string $key, float $oldValue, float $newValue, int $ttl): bool
    {
        $oldValue = (int) ($oldValue * $this->fixPrecisionRate);
        $newValue = (int) ($newValue * $this->fixPrecisionRate);
        return  (bool)apcu_cas($key, $oldValue, $newValue);
    }

    public function get(string $key): ?float
    {
        // Simulate dirty reading scenarios in this ApcuStorage class
        if ($this->remainingDirtyReadCount > 0 && $this->dirtyReadValue !== null) {
            $this->remainingDirtyReadCount--;
            return $this->dirtyReadValue;
        }

        $readValue = apcu_fetch($key);
        if ($readValue === false) {
            return null;
        }
        
        $readValue = (float) ($readValue / $this->fixPrecisionRate);
        $this->dirtyReadValue = $readValue;
        $this->remainingDirtyReadCount = $this->dirtyReadCount;

        return $readValue;
    }
}
