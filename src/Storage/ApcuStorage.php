<?php

declare(strict_types=1);

namespace Yiisoft\Yii\RateLimiter\Storage;

use InvalidArgumentException;

/**
 * To use this storage, the [APCu PHP extension](http://www.php.net/apcu) must be loaded,
 * And you should add "apc.enabled = 1" to your php.ini.
 * In order to enable APCu for CLI you should add "apc.enable_cli = 1" to your php.ini.
 */
final class ApcuStorage implements StorageInterface
{
    private const DEFAULT_FIX_PRECISION_RATE = 1000;

    /**
     * @param int $fixPrecisionRate 
     * Apcu_cas of ACPu does not support float,  and yet supports int.
     * APCu's stored value multiply by $fixPrecisionRate converts to int,
     * AND the getter's value divide by $fixPrecisionRate converts to float.
     * So use it to improve precision.
     */
    public function __construct(
        private int $fixPrecisionRate = self::DEFAULT_FIX_PRECISION_RATE
    ) {
    }

    public function saveIfNotExists(string $key, int|float $value, int $ttl): bool
    {
        $value = (int) ($value * $this->fixPrecisionRate);

        return apcu_add($key, $value, $ttl);
    }

    public function saveCompareAndSwap(string $key, int|float $oldValue, int|float $newValue, int $ttl): bool
    {
        $oldValue = (int) ($oldValue * $this->fixPrecisionRate);
        $newValue = (int) ($newValue * $this->fixPrecisionRate);

        return apcu_cas($key, $oldValue, $newValue);
    }

    public function get(string $key): ?float
    {
        $value = apcu_fetch($key);
        if (!is_int($value) && !is_float($value) && $value !== false) {
            throw new InvalidArgumentException('The value is not supported by ApcuStorage, it must be int, float.');
        }

        if ($value !== false) {
            $value = (float)$value / $this->fixPrecisionRate;
        } else {
            $value = null;
        }
        return $value;
    }
}
