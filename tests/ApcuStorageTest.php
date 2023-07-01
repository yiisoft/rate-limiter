<?php

declare(strict_types=1);

namespace Yiisoft\Yii\RateLimiter\Tests;

use InvalidArgumentException;
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
    public static function setUpBeforeClass(): void
    {
        if (!extension_loaded('apcu')) {
            self::markTestSkipped('Required extension "apcu" is not loaded');
        }

        if (!ini_get('apc.enable_cli')) {
            self::markTestSkipped('APC is installed but not enabled. Enable with "apc.enable_cli=1" from php.ini. Skipping.');
        }
    }

    public function testSaveIfNotExistsWithExistsKey(): void
    {
        $storage = $this->getStorage();

        $value = (new FrozenTimeTimer())->nowInMilliseconds();
        $storage->saveIfNotExists('exists_key', $value, self::DEFAULT_TTL);

        $result = $storage->saveIfNotExists('exists_key', $value, self::DEFAULT_TTL);

        $this->assertFalse($result);
    }

    public function testSaveCompareAndSwapWithNewKey(): void
    {
        $storage = $this->getStorage();

        $newValue = (new FrozenTimeTimer())->nowInMilliseconds();
        $oldValue = (int) $storage->get('new_key');

        $result = $storage->saveCompareAndSwap(
            'new_key', 
            $oldValue, 
            $newValue, 
            self::DEFAULT_TTL
        );

        $this->assertFalse($result);
    }

    public function testSaveCompareAndSwapWithExistsKeyButOldValueDiffrent(): void
    {
        $storage = $this->getStorage();

        $oldValue = (new FrozenTimeTimer())->nowInMilliseconds();
        $storage->saveIfNotExists('exists_key', $oldValue, self::DEFAULT_TTL);

        $oldValue = $oldValue + 200;

        $newValue = $oldValue + 100;

        $result = $storage->saveCompareAndSwap(
            'exists_key', 
            $oldValue, 
            $newValue, 
            self::DEFAULT_TTL
        );

        $this->assertFalse($result);
    }

    public function testInvalidArgumentExceptionWithGet(): void
    {
        apcu_add('key', 'string_value', parent::DEFAULT_TTL);

        $this->expectException(InvalidArgumentException::class);

        $storage = $this->getStorage();
        $storage->get('key');
    }
}
