<?php

declare(strict_types=1);

namespace Yiisoft\Yii\RateLimiter\Policy;

use Psr\Http\Message\ServerRequestInterface;

final class LimitAlways implements LimitPolicyInterface
{
    public function fingerprint(ServerRequestInterface $request): string
    {
        return sha1(strtolower($request->getMethod() . $request->getUri()->getPath()));
    }
}
