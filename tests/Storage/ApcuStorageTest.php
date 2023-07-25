<?php

declare(strict_types=1);

namespace Yiisoft\Yii\RateLimiter\Tests\Storage;

use DateTimeImmutable;
use Yiisoft\Yii\RateLimiter\Storage\ApcuStorage;
use Yiisoft\Yii\RateLimiter\Storage\StorageInterface;

final class ApcuStorageTest extends StorageTest
{
    protected function getStorage(): StorageInterface
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

        $value = (new DateTimeImmutable())->format('U.u') * 1000;
        $storage->saveIfNotExists('exists_key', $value, self::DEFAULT_TTL);

        $result = $storage->saveIfNotExists('exists_key', $value, self::DEFAULT_TTL);

        $this->assertFalse($result);
    }

    public function testSaveCompareAndSwapWithNewKey(): void
    {
        $storage = $this->getStorage();

        $newValue = (new DateTimeImmutable())->format('U.u') * 1000;
        $oldValue = (int) $storage->get('new_key');

        $result = $storage->saveCompareAndSwap(
            'new_key',
            $oldValue,
            $newValue,
            self::DEFAULT_TTL
        );

        $this->assertFalse($result);
    }

    public function testSaveCompareAndSwapWithExistsKeyButOldValueDifferent(): void
    {
        $storage = $this->getStorage();

        $oldValue = (new DateTimeImmutable())->format('U.u') * 1000;
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

    public function testStringValueInCache(): void
    {
        apcu_add('key', 'string_value', parent::DEFAULT_TTL);
        $storage = $this->getStorage();

        $value = $storage->get('key');

        $this->assertNull($value);
    }
}
