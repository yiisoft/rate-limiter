<?php

declare(strict_types=1);

namespace Yiisoft\Yii\RateLimiter\Tests\Storage;

use DateTimeImmutable;
use PHPUnit\Framework\TestCase;
use Yiisoft\Yii\RateLimiter\Storage\StorageInterface;

abstract class StorageTest extends TestCase
{
    protected const DEFAULT_TTL = 86400;

    abstract protected function getStorage(): StorageInterface;

    abstract protected function clearStorage(): bool;

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

        $want = round((float)(new DateTimeImmutable())->format('U.u') * 1000);

        $storage->saveIfNotExists('exists_key', $want, self::DEFAULT_TTL);

        $result = $storage->get('exists_key');

        $this->assertEquals($want, $result);
    }

    public function testSaveIfNotExistsWithNewKey(): void
    {
        $storage = $this->getStorage();

        $value = round((float)(new DateTimeImmutable())->format('U.u') * 1000);

        $result = $storage->saveIfNotExists('new_key', $value, self::DEFAULT_TTL);

        $this->assertTrue($result);
    }

    public function testSaveCompareAndSwapWithExistsKeyAndOldValueSame(): void
    {
        $storage = $this->getStorage();

        $oldValue = round((float)(new DateTimeImmutable())->format('U.u') * 1000);
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
