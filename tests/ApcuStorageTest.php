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
    public static function setUpBeforeClass(): void
    {
        if (!extension_loaded('apcu')) {
            self::markTestSkipped('Required extension "apcu" is not loaded');
        }

        if (!ini_get('apc.enable_cli')) {
            self::markTestSkipped('APC is installed but not enabled. Enable with "apc.enable_cli=1" from php.ini. Skipping.');
        }
    }

    public function testInvalidArgumentExceptionWithGet(): void
    {
        $storage = new ApcuStorage();

        apcu_add('key', 'string_value', parent::DEFAULT_TTL);

        $this->expectException(InvalidArgumentException::class);
        $storage->get('key');
    }
}
