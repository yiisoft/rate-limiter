<?php

declare(strict_types=1);

namespace Yiisoft\Yii\RateLimiter\Policy;

use Closure;
use Psr\Http\Message\ServerRequestInterface;

final class LimitCallback implements LimitPolicyInterface
{
    public function __construct(private Closure $receiver)
    {
    }

    public function fingerprint(ServerRequestInterface $request): string
    {
        $id = ($this->receiver)($request);

        if (!is_string($id) || '' === $id) {
            throw new \InvalidArgumentException('The id must be a non-empty-string.');
        }

        return $id;
    }
}
