<?php

declare(strict_types=1);

namespace Yiisoft\Yii\RateLimiter\Policy;

use Psr\Http\Message\ServerRequestInterface;

final class LimitingFunction implements LimitingPolicy
{
    /**
     * @psalm-var callable(ServerRequestInterface): string
     */
    private $receiver;

    /**
     * @psalm-param callable(ServerRequestInterface): string $receiver
     */
    public function __construct(callable $receiver)
    {
        $this->receiver = $receiver;
    }

    public function fingerprint(ServerRequestInterface $request): string
    {
        $id = ($this->receiver)($request);

        /** @psalm-suppress DocblockTypeContradiction */
        if (!is_string($id) || '' === $id) {
            throw new \InvalidArgumentException('The id must be a non-empty-string.');
        }

        return $id;
    }
}
