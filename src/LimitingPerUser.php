<?php

declare(strict_types=1);

namespace Yiisoft\Yii\RateLimiter;

use Psr\Http\Message\ServerRequestInterface;

final class LimitingPerUser implements LimitingPolicy
{
    public function fingerprint(ServerRequestInterface $request): string
    {
        return sha1(strtolower($request->getMethod() . '-' . $request->getUri()->getPath() . '-' . $this->getIp($request)));
    }

    private function getIp(ServerRequestInterface $request): string
    {
        $server = $request->getServerParams();

        return trim($server['REMOTE_ADDR'] ?? '', '[]');
    }
}
