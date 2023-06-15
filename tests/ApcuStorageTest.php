<?php

declare(strict_types=1);

namespace Yiisoft\Yii\RateLimiter\Tests;

use InvalidArgumentException;
use Yiisoft\Yii\RateLimiter\Exception\CannotUseException;
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

    public function testCannotUseExceptionWithAPCuNotEnabled(): void
    {
        $this->expectException(CannotUseException::class);
        throw new CannotUseException;
    }

    public function testInvalidArgumentExceptionWithSaveIfNotExists(): void
    {
        $storage = new ApcuStorage();

        $this->expectException(InvalidArgumentException::class);
        $storage->saveIfNotExists('key', 'string_value', parent::DEFAULT_TTL);
    }

    public function testInvalidArgumentExceptionWithSaveCompareAndSwapOldValue(): void
    {
        $storage = new ApcuStorage();

        $this->expectException(InvalidArgumentException::class);
        $storage->saveCompareAndSwap('key', 'old_string_value', 1, parent::DEFAULT_TTL);
    }

    public function testInvalidArgumentExceptionWithSaveCompareAndSwapNewValue(): void
    {
        $storage = new ApcuStorage();

        $this->expectException(InvalidArgumentException::class);
        $storage->saveCompareAndSwap('key', 1, 'new_string_value', parent::DEFAULT_TTL);
    }

    public function testInvalidArgumentExceptionWithGet(): void
    {
        $storage = new ApcuStorage();

        apcu_add('key', 'string_value', parent::DEFAULT_TTL);

        $this->expectException(InvalidArgumentException::class);
        $storage->get('key');
    }
}
