<?php

declare(strict_types=1);

namespace Yiisoft\Yii\RateLimiter;

use Psr\SimpleCache\CacheInterface;

/**
 * Counter implements generic cell rate limit algorithm (GCRA) that ensures that after reaching the limit further
 * increments are distributed equally.
 *
 * @link https://en.wikipedia.org/wiki/Generic_cell_rate_algorithm
 */
final class Counter implements CounterInterface
{
    private const DEFAULT_TTL = 86400;

    private const ID_PREFIX = 'rate-limiter-';

    private const MILLISECONDS_PER_SECOND = 1000;

    /**
     * @var int period to apply limit to
     */
    private int $periodInMilliseconds;

    private int $limit;

    /**
     * @var float maximum interval before next increment
     * In GCRA it is known as emission interval.
     */
    private float $incrementIntervalInMilliseconds;

    private ?string $id = null;

    private CacheInterface $storage;

    private int $ttlInSeconds = self::DEFAULT_TTL;

    /**
     * @var int last increment time
     * In GCRA it's known as arrival time
     */
    private int $lastIncrementTimeInMilliseconds;

    /**
     * @param int $limit maximum number of increments that could be performed before increments are limited
     * @param int $periodInSeconds period to apply limit to
     * @param CacheInterface $storage
     */
    public function __construct(int $limit, int $periodInSeconds, CacheInterface $storage)
    {
        if ($limit < 1) {
            throw new \InvalidArgumentException('The limit must be a positive value.');
        }

        if ($periodInSeconds < 1) {
            throw new \InvalidArgumentException('The period must be a positive value.');
        }

        $this->limit = $limit;
        $this->periodInMilliseconds = $periodInSeconds * self::MILLISECONDS_PER_SECOND;
        $this->storage = $storage;

        $this->incrementIntervalInMilliseconds = (float)($this->periodInMilliseconds / $this->limit);
    }

    public function setId(string $id): void
    {
        $this->id = $id;
    }

    /**
     * @param int $secondsTTL cache TTL that is used to store counter values
     * Default is one day.
     * Note that period can not exceed TTL.
     */
    public function setTtlInSeconds(int $secondsTTL): void
    {
        $this->ttlInSeconds = $secondsTTL;
    }

    public function getCacheKey(): string
    {
        return self::ID_PREFIX . $this->id;
    }

    public function incrementAndGetState(): CounterState
    {
        if ($this->id === null) {
            throw new \LogicException('The counter ID should be set');
        }

        $this->lastIncrementTimeInMilliseconds = $this->currentTimeInMilliseconds();
        $theoreticalNextIncrementTime = $this->calculateTheoreticalNextIncrementTime(
            $this->getLastStoredTheoreticalNextIncrementTime()
        );
        $remaining = $this->calculateRemaining($theoreticalNextIncrementTime);
        $resetAfter = $this->calculateResetAfter($theoreticalNextIncrementTime);

        if ($remaining >= 1) {
            $this->storeTheoreticalNextIncrementTime($theoreticalNextIncrementTime);
        }

        return new CounterState($this->limit, $remaining, $resetAfter);
    }

    /**
     * @param float $storedTheoreticalNextIncrementTime
     *
     * @return float theoretical increment time that would be expected from equally spaced increments at exactly rate limit
     * In GCRA it is known as TAT, theoretical arrival time.
     */
    private function calculateTheoreticalNextIncrementTime(float $storedTheoreticalNextIncrementTime): float
    {
        return max($this->lastIncrementTimeInMilliseconds, $storedTheoreticalNextIncrementTime) + $this->incrementIntervalInMilliseconds;
    }

    /**
     * @param float $theoreticalNextIncrementTime
     *
     * @return int the number of remaining requests in the current time period
     */
    private function calculateRemaining(float $theoreticalNextIncrementTime): int
    {
        $incrementAllowedAt = $theoreticalNextIncrementTime - $this->periodInMilliseconds;

        return (int)(round($this->lastIncrementTimeInMilliseconds - $incrementAllowedAt) / $this->incrementIntervalInMilliseconds);
    }

    private function getLastStoredTheoreticalNextIncrementTime(): float
    {
        return $this->storage->get($this->getCacheKey(), (float)$this->lastIncrementTimeInMilliseconds);
    }

    private function storeTheoreticalNextIncrementTime(float $theoreticalNextIncrementTime): void
    {
        $this->storage->set($this->getCacheKey(), $theoreticalNextIncrementTime, $this->ttlInSeconds);
    }

    /**
     * @param float $theoreticalNextIncrementTime
     *
     * @return int timestamp to wait until the rate limit resets
     */
    private function calculateResetAfter(float $theoreticalNextIncrementTime): int
    {
        return (int)($theoreticalNextIncrementTime / self::MILLISECONDS_PER_SECOND);
    }

    private function currentTimeInMilliseconds(): int
    {
        return (int)round(microtime(true) * self::MILLISECONDS_PER_SECOND);
    }
}
