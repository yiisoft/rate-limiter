<?php

declare(strict_types=1);

namespace Yiisoft\Yii\RateLimiter\Policy;

use Psr\Http\Message\ServerRequestInterface;

interface LimitingPolicy
{
    public function fingerprint(ServerRequestInterface $request): string;
}
