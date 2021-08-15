<?php

declare(strict_types=1);

namespace Yiisoft\Yii\RateLimiter;

use InvalidArgumentException;
use Yiisoft\Yii\RateLimiter\Storage\StorageInterface;
use Yiisoft\Yii\RateLimiter\Time\MicrotimeTimer;
use Yiisoft\Yii\RateLimiter\Time\TimerInterface;

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
     * @var int Period to apply limit to.
     */
    private int $periodInMilliseconds;

    private int $limit;

    /**
     * @var float Maximum interval before next increment.
     * In GCRA it is known as emission interval.
     */
    private float $incrementIntervalInMilliseconds;

    private StorageInterface $storage;

    private int $storageTtlInSeconds;
    private string $storagePrefix;

    private TimerInterface $timer;

    /**
     * @param StorageInterface $storage Storage to use for counter values.
     * @param int $limit Maximum number of increments that could be performed before increments are limited.
     * @param int $periodInSeconds Period to apply limit to.
     * @param int $ttlInSeconds Storage TTL. Should be higher than `$periodInSeconds`.
     * @param string $storagePrefix Storage prefix.
     * @param TimerInterface|null $timer Timer instance to get current time from.
     */
    public function __construct(
        StorageInterface $storage,
        int $limit,
        int $periodInSeconds,
        int $ttlInSeconds = self::DEFAULT_TTL,
        string $storagePrefix = self::ID_PREFIX,
        ?TimerInterface $timer = null
    ) {
        if ($limit < 1) {
            throw new InvalidArgumentException('The limit must be a positive value.');
        }

        if ($periodInSeconds < 1) {
            throw new InvalidArgumentException('The period must be a positive value.');
        }

        $this->limit = $limit;
        $this->periodInMilliseconds = $periodInSeconds * self::MILLISECONDS_PER_SECOND;
        $this->storage = $storage;
        $this->storageTtlInSeconds = $ttlInSeconds;
        $this->storagePrefix = $storagePrefix;
        $this->timer = $timer ?: new MicrotimeTimer();

        $this->incrementIntervalInMilliseconds = (float)($this->periodInMilliseconds / $this->limit);
    }

    /**
     * {@inheritdoc}
     */
    public function hit(string $id): CounterState
    {
        // Last increment time.
        // In GCRA it's known as arrival time.
        $lastIncrementTimeInMilliseconds = $this->timer->nowInMilliseconds();

        $theoreticalNextIncrementTime = $this->calculateTheoreticalNextIncrementTime(
            $lastIncrementTimeInMilliseconds,
            $this->getLastStoredTheoreticalNextIncrementTime($id, $lastIncrementTimeInMilliseconds)
        );

        $remaining = $this->calculateRemaining($lastIncrementTimeInMilliseconds, $theoreticalNextIncrementTime);
        $resetAfter = $this->calculateResetAfter($theoreticalNextIncrementTime);

        if ($remaining >= 1) {
            $this->storeTheoreticalNextIncrementTime($id, $theoreticalNextIncrementTime);
        }

        return new CounterState($this->limit, $remaining, $resetAfter);
    }

    /**
     * @param int $lastIncrementTimeInMilliseconds
     * @param float $storedTheoreticalNextIncrementTime
     *
     * @return float Theoretical increment time that would be expected from equally spaced increments at exactly rate
     * limit. In GCRA it is known as TAT, theoretical arrival time.
     */
    private function calculateTheoreticalNextIncrementTime(
        int $lastIncrementTimeInMilliseconds,
        float $storedTheoreticalNextIncrementTime
    ): float {
        return max($lastIncrementTimeInMilliseconds, $storedTheoreticalNextIncrementTime) +
            $this->incrementIntervalInMilliseconds;
    }

    /**
     * @param int $lastIncrementTimeInMilliseconds
     * @param float $theoreticalNextIncrementTime
     *
     * @return int The number of remaining requests in the current time period.
     */
    private function calculateRemaining(int $lastIncrementTimeInMilliseconds, float $theoreticalNextIncrementTime): int
    {
        $incrementAllowedAt = $theoreticalNextIncrementTime - $this->periodInMilliseconds;

        return (int)(
            round($lastIncrementTimeInMilliseconds - $incrementAllowedAt) /
            $this->incrementIntervalInMilliseconds
        );
    }

    private function getLastStoredTheoreticalNextIncrementTime(string $id, int $lastIncrementTimeInMilliseconds): float
    {
        return (float)$this->storage->get($this->getStorageKey($id), $lastIncrementTimeInMilliseconds);
    }

    private function storeTheoreticalNextIncrementTime(string $id, float $theoreticalNextIncrementTime): void
    {
        $this->storage->save($this->getStorageKey($id), $theoreticalNextIncrementTime, $this->storageTtlInSeconds);
    }

    /**
     * @param float $theoreticalNextIncrementTime
     *
     * @return int Timestamp to wait until the rate limit resets.
     */
    private function calculateResetAfter(float $theoreticalNextIncrementTime): int
    {
        return (int)($theoreticalNextIncrementTime / self::MILLISECONDS_PER_SECOND);
    }

    /**
     * @param string $id
     *
     * @return string Storage key used to store the next increment time.
     */
    private function getStorageKey(string $id): string
    {
        return $this->storagePrefix . $id;
    }
}
