<?php

declare(strict_types=1);

namespace Yiisoft\Yii\RateLimiter\Storage;

use function is_float;
use function is_int;

/**
 * To use this storage, the [APCu PHP extension](https://www.php.net/apcu) must be loaded,
 * And you should add "apc.enabled = 1" to your php.ini.
 * In order to enable APCu for CLI you should add "apc.enable_cli = 1" to your php.ini.
 */
final class ApcuStorage implements StorageInterface
{
    private const DEFAULT_FIX_PRECISION_RATE = 1000;

    /**
     * Apcu_cas of ACPu does not support float,  and yet supports int.
     * APCu's stored value multiply by $fixPrecisionRate converts to int,
     * AND the getter's value divide by $fixPrecisionRate converts to float.
     * So use it to improve precision.
     */
    public function __construct(
        private int $fixPrecisionRate = self::DEFAULT_FIX_PRECISION_RATE
    ) {
    }

    public function saveIfNotExists(string $key, float $value, int $ttl): bool
    {
        $value *= $this->fixPrecisionRate;

        return apcu_add($key, (int) $value, $ttl);
    }

    public function saveCompareAndSwap(string $key, float $oldValue, float $newValue, int $ttl): bool
    {
        $oldValue *= $this->fixPrecisionRate;
        $newValue *= $this->fixPrecisionRate;

        return apcu_cas($key, (int) $oldValue, (int) $newValue);
    }

    public function get(string $key): ?float
    {
        /** @psalm-suppress MixedAssignment */
        $value = apcu_fetch($key);

        return (is_int($value) || is_float($value))
            ? (float) $value / $this->fixPrecisionRate
            : null;
    }
}
