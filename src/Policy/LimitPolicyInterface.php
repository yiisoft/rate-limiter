<?php

declare(strict_types=1);

namespace Yiisoft\Yii\RateLimiter\Policy;

use Psr\Http\Message\ServerRequestInterface;

/**
 * Defines policy for limiting requests i.e. which requests should be hit-counted together and which should be
 * counted separately.
 *
 * For example, a user ID-based fingerprint will make rate limiting apply to current user requests only. An API token-based
 * fingerprint will make rate limiting to apply to all requests with the same token.
 */
interface LimitPolicyInterface
{
    /**
     * Returns request fingerprint. Two requests with the same fingerprint increase request counter. Requests
     * with different fingerprints are counted and limited separately.
     *
     * @param ServerRequestInterface $request Request to get fingerprint for.
     *
     * @return string A fingerprint based on the request information.
     */
    public function fingerprint(ServerRequestInterface $request): string;
}
