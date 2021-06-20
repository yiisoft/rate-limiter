<?php

declare(strict_types=1);

namespace Yiisoft\Yii\RateLimiter\Policy;

use Psr\Http\Message\ServerRequestInterface;

/**
 * Defines what to use to determine if the same client is making a hit when rate limiting.
 * For example, user ID will make rate limiting apply to current user only and an API token
 * will make rate limiting to apply to all users with the same token.
 */
interface LimitPolicyInterface
{
    public function fingerprint(ServerRequestInterface $request): string;
}
