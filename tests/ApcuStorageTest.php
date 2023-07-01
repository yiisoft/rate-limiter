<?php

declare(strict_types=1);

namespace Yiisoft\Yii\RateLimiter\Tests;

use InvalidArgumentException;
use Yiisoft\Yii\RateLimiter\Exception\CannotUseException;
use Yiisoft\Yii\RateLimiter\Storage\ApcuStorage;
use Yiisoft\Yii\RateLimiter\Storage\StorageInterface;

final class ApcuStorageTest extends StorageTest
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

    public function testInvalidArgumentExceptionWithGet(): void
    {
        $storage = new ApcuStorage();

        apcu_add('key', 'string_value', parent::DEFAULT_TTL);

        $this->expectException(InvalidArgumentException::class);
        $storage->get('key');
    }
}
