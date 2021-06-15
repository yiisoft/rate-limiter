<?php

declare(strict_types=1);

namespace Yiisoft\Yii\RateLimiter;

use Psr\Http\Message\ServerRequestInterface;

interface LimitingPolicy
{
    public function fingerprint(ServerRequestInterface $request): string;
}
