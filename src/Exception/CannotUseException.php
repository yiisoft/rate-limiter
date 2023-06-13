<?php

declare(strict_types=1);

namespace Yiisoft\Yii\RateLimiter\Exception;

use RuntimeException;
use Yiisoft\Yii\RateLimiter\Exception\RateLimiterExceptionInterface;

final class CannotUseException extends RuntimeException implements RateLimiterExceptionInterface
{
}
