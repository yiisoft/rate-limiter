<?php

declare(strict_types=1);

namespace Yiisoft\Yii\RateLimiter;

use Psr\Http\Message\ServerRequestInterface;

final class LimitingAll implements LimitingPolicy
{
    public function fingerprint(ServerRequestInterface $request): string
    {
        return sha1(strtolower($request->getMethod() . '-' . $request->getUri()->getPath()));
    }
}
