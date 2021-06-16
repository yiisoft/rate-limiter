<?php

declare(strict_types=1);

namespace Yiisoft\Yii\RateLimiter\Tests;

use Nyholm\Psr7\ServerRequest;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;
use Yiisoft\Http\Method;
use Yiisoft\Yii\RateLimiter\Policy\LimitingFunction;

final class LimitingFunctionTest extends TestCase
{
    public function testThatExceptionAreThrowIfIdIsNotAString(): void
    {
        self::expectDeprecationMessage('The id must be a non-empty-string.');
        self::expectException(\InvalidArgumentException::class);
        (new LimitingFunction(function (ServerRequestInterface $_request): string {
            return '';
        }))->fingerprint(new ServerRequest(Method::GET, '/'));
    }
}
