<?php

declare(strict_types=1);

namespace Yiisoft\Yii\RateLimiter\Tests;

use InvalidArgumentException;
use Yiisoft\Cache\ArrayCache;
use Yiisoft\Yii\RateLimiter\Storage\SimpleCacheStorage;
use Yiisoft\Yii\RateLimiter\Storage\StorageInterface;

final class SimpleCacheStorageTest extends StorageTest
{
    protected function createStorage(): StorageInterface
    {
        return new SimpleCacheStorage(new ArrayCache());
    }

    protected function clearStorage(): bool
    {
        return true;
    }

    public function testInvalidArgumentExceptionWithGet(): void
    {
        $storage = $this->getStorage();
        $storage->saveIfNotExists('key', 'string_value', parent::DEFAULT_TTL);

        $this->expectException(InvalidArgumentException::class);
        $storage->get('key');
    }
}
