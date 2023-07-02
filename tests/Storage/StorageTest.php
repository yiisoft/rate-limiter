<?php

declare(strict_types=1);

namespace Yiisoft\Yii\RateLimiter\Tests\Storage;

use PHPUnit\Framework\TestCase;
use Yiisoft\Yii\RateLimiter\Storage\StorageInterface;
use Yiisoft\Yii\RateLimiter\Tests\Fixtures\FrozenTimeTimer;

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

        $this->assertNull($storage->get('missing_key'));
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

    public function testSaveCompareAndSwapWithExistsKeyAndOldValueSame(): void
    {
        $storage = $this->getStorage();

        $oldValue = (new FrozenTimeTimer())->nowInMilliseconds();
        $storage->saveIfNotExists('exists_key', $oldValue, self::DEFAULT_TTL);

        $newValue = $oldValue + 100;

        $result = $storage->saveCompareAndSwap(
            'exists_key', 
            $oldValue, 
            $newValue, 
            self::DEFAULT_TTL
        );

        $this->assertTrue($result);
    }
}
