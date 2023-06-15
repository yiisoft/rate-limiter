<?php

declare(strict_types=1);

namespace Yiisoft\Yii\RateLimiter\Tests;

use PHPUnit\Framework\TestCase;
use Yiisoft\Yii\RateLimiter\Storage\StorageInterface;
use Yiisoft\Yii\RateLimiter\Tests\FrozenTimeTimer;

abstract class StorageTest extends TestCase
{
    protected StorageInterface $storage;

    protected const DEFAULT_TTL = 86400;

    protected abstract function createStorage(): StorageInterface;

    protected abstract function clearStorage(): bool;

    protected function getStorage(): StorageInterface
    {
        if (empty($this->storage)) {
            $this->storage = $this->createStorage();
        }

        return $this->storage;
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->clearStorage();
    }

    public function testGetKeyWithMissingKey(): void
    {
        $storage = $this->getStorage();

        $this->assertFalse($storage->get('missing_key'));
    }

    public function testGetKeyWithExistsKey(): void
    {
        $storage = $this->getStorage();

        $want = (new FrozenTimeTimer())->nowInMilliseconds();

        $storage->saveIfNotExists('exists_key', $want, self::DEFAULT_TTL);

        $result = $storage->get('exists_key');

        $this->assertEquals($result, $want);
    }

    public function testSaveIfNotExistsWithNewKey(): void
    {
        $storage = $this->getStorage();

        $value = (new FrozenTimeTimer())->nowInMilliseconds();

        $result = $storage->saveIfNotExists('new_key', $value, self::DEFAULT_TTL);

        $this->assertTrue($result);
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

        $new_value = (new FrozenTimeTimer())->nowInMilliseconds();
        $old_value = (int) $storage->get('new_key');

        $result = $storage->saveCompareAndSwap(
            'new_key', 
            $old_value, 
            $new_value, 
            self::DEFAULT_TTL
        );

        $this->assertFalse($result);
    }

    public function testSaveCompareAndSwapWithExistsKeyButOldValueDiffrent(): void
    {
        $storage = $this->getStorage();

        $old_value = (new FrozenTimeTimer())->nowInMilliseconds();
        $storage->saveIfNotExists('exists_key', $old_value, self::DEFAULT_TTL);

        $old_value = $old_value + 200;

        $new_value = $old_value + 100;

        $result = $storage->saveCompareAndSwap(
            'exists_key', 
            $old_value, 
            $new_value, 
            self::DEFAULT_TTL
        );

        $this->assertFalse($result);
    }

    public function testSaveCompareAndSwapWithExistsKeyAndOldValueSame(): void
    {
        $storage = $this->getStorage();

        $old_value = (new FrozenTimeTimer())->nowInMilliseconds();
        $storage->saveIfNotExists('exists_key', $old_value, self::DEFAULT_TTL);

        $new_value = $old_value + 100;

        $result = $storage->saveCompareAndSwap(
            'exists_key', 
            $old_value, 
            $new_value, 
            self::DEFAULT_TTL
        );

        $this->assertTrue($result);
    }
}
