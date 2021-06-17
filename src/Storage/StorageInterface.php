<?php

declare(strict_types=1);

namespace Yiisoft\Yii\RateLimiter\Storage;

interface StorageInterface
{
    /**
     * @param string $key
     * @param mixed $value
     * @param int $ttl
     *
     * @return void
     */
    public function save(string $key, $value, int $ttl): void;

    /**
     * @param string $key
     * @param mixed $default
     *
     * @return mixed
     */
    public function get(string $key, $default = null);
}
