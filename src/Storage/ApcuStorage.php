<?php

declare(strict_types=1);

namespace Yiisoft\Yii\RateLimiter\Storage;

use InvalidArgumentException;
use Yiisoft\Yii\RateLimiter\Exception\CannotUseException;

final class ApcuStorage implements StorageInterface
{
    private const DEFAULT_FIX_PRECISION_RATE = 1000;

    /**
     * @param int $fixPrecisionRate 
     * floating point is not supported by apcu_cas of ACPu, so use it to improve precision.
     */
    public function __construct(
        private int $fixPrecisionRate = self::DEFAULT_FIX_PRECISION_RATE
    ) {
        /**
         * To use this implementation class, the [APCu PHP extension](http://www.php.net/apcu) must be loaded,
         * And you should add "apc.enabled = 1" to your php.ini.
         * In order to enable APCu for CLI you should add "apc.enable_cli = 1" to your php.ini.
         */
        if (!extension_loaded('apcu') || !ini_get('apc.enabled')) {
            throw new CannotUseException('APCu extension is not loaded or not enabled.');
        }
    }

    public function saveIfNotExists(string $key, mixed $value, int $ttl): bool
    {
        if ((!is_int($value)) && !is_float($value)) {
            throw new InvalidArgumentException('The value is not supported by ApcuStorage,it must be int or float');
        }

        $value = (int) ($value * $this->fixPrecisionRate);

        return apcu_add($key, $value, $ttl);
    }

    public function saveCompareAndSwap(string $key, mixed $oldValue, mixed $newValue, int $ttl): bool
    {
        if ((!is_int($oldValue)) && !is_float($oldValue)) {
            throw new InvalidArgumentException('The oldValue is not supported by ApcuStorage,it must be int or float');
        }

        if ((!is_int($newValue)) && !is_float($newValue)) {
            throw new InvalidArgumentException('The newValue is not supported by ApcuStorage,it must be int or float');
        }

        $oldValue = (int) ($oldValue * $this->fixPrecisionRate);
        $newValue = (int) ($newValue * $this->fixPrecisionRate);

        return apcu_cas($key, $oldValue, $newValue);
    }

    public function get(string $key): mixed
    {
        $value = apcu_fetch($key);

        if ((!is_int($value)) && !is_float($value) && !is_bool($value)) {
            throw new InvalidArgumentException('The value is not supported by ApcuStorage,it must be bool, int or float');
        }

        if ($value != false) {
            $value = (float)$value / $this->fixPrecisionRate;
        }
        return $value;
    }
}
