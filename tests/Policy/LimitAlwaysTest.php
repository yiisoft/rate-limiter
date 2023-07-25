<?php

declare(strict_types=1);

namespace Yiisoft\Yii\RateLimiter\Tests\Policy;

use Nyholm\Psr7\ServerRequest;
use PHPUnit\Framework\TestCase;
use Yiisoft\Yii\RateLimiter\Policy\LimitAlways;

final class LimitAlwaysTest extends TestCase
{
    public function testFingerprint(): void
    {
        $request = new ServerRequest('GET', '/');
        $limit = new LimitAlways();
        $this->assertSame('b23f2dca7eee40165d5e887dce6147afddf414f5', $limit->fingerprint($request));
    }

    public function testMultibyteFingerprint(): void
    {
        $request = new ServerRequest('GET', '/mĄkA');
        $limit = new LimitAlways();
        $this->assertSame('8d2c34db1f28662d5e8e07fec60245b3b20d041b', $limit->fingerprint($request));
    }
}
