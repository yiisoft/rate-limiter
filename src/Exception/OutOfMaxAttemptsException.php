<?php

declare(strict_types=1);

namespace Yiisoft\Yii\RateLimiter\Exception;

use RuntimeException;
use Yiisoft\Yii\RateLimiter\Exception\RateLimiterExceptionInterface;

final class OutOfMaxAttemptsException extends RuntimeException implements RateLimiterExceptionInterface
{
}