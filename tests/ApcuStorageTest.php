<?php

declare(strict_types=1);

namespace Yiisoft\Yii\RateLimiter\Tests;

use Yiisoft\Yii\RateLimiter\Storage\ApcuStorage;
use Yiisoft\Yii\RateLimiter\Storage\StorageInterface;

class ApcuStorageTest extends StorageTest
{
    protected function createStorage(): StorageInterface
    {
        return new ApcuStorage();
    }
    protected function clearStorage(): bool
    {
        return apcu_clear_cache();
    }
}
