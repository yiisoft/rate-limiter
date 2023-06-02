<?php

declare(strict_types=1);

namespace Yiisoft\Yii\RateLimiter\Tests;

use InvalidArgumentException;
use Nyholm\Psr7\ServerRequest;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;
use Yiisoft\Http\Method;
use Yiisoft\Yii\RateLimiter\Policy\LimitCallback;

final class LimitingFunctionTest extends TestCase
{
    public function testThatExceptionAreThrowIfIdIsNotAString(): void
    {
        $policy = new LimitCallback(fn(ServerRequestInterface $_request): string => '');
        $request = new ServerRequest(Method::GET, '/');

        $this->expectExceptionMessage('The id must be a non-empty-string.');
        $this->expectException(InvalidArgumentException::class);
        $policy->fingerprint($request);
    }
}
