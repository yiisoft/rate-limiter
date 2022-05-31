<?php

declare(strict_types=1);

namespace Yiisoft\Yii\RateLimiter\Policy;

use Psr\Http\Message\ServerRequestInterface;

final class LimitPerIp implements LimitPolicyInterface
{
    public function fingerprint(ServerRequestInterface $request): string
    {
        return sha1(mb_strtolower($request->getMethod() . '-' . $request
                ->getUri()
                ->getPath() . '-' . $this->getIp($request)));
    }

    private function getIp(ServerRequestInterface $request): string
    {
        /** @psalm-var array{REMOTE_ADDR?: string} $server */
        $server = $request->getServerParams();

        return trim($server['REMOTE_ADDR'] ?? '', '[]');
    }
}
