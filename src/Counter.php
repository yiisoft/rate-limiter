<?php

declare(strict_types=1);

namespace Yiisoft\Yii\RateLimiter;

use InvalidArgumentException;
use Psr\Clock\ClockInterface;
use Yiisoft\Yii\RateLimiter\Storage\StorageInterface;
use Yiisoft\Yii\RateLimiter\Time\SystemClock;

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
    private const DEFAULT_MAX_CAS_ATTEMPTS = 10;

    /**
     * @var int Period to apply limit to.
     */
    private int $periodInMilliseconds;

    /**
     * @var float Maximum interval before the next increment.
     * In GCRA it's known as an emission interval.
     */
    private float $incrementIntervalInMilliseconds;
    private ClockInterface $timer;

    /**
     * @param StorageInterface $storage Storage to use for counter values.
     * @param int $limit A maximum number of increments that could be performed before increments are limited.
     * @param int $periodInSeconds Period to apply limit to.
     * @param int $storageTtlInSeconds Storage TTL. Should be higher than `$periodInSeconds`.
     * @param string $storagePrefix Storage prefix.
     * @param ClockInterface|null $timer Timer instance to get current time from.
     * @param int $maxCasAttempts Maximum number of times to retry saveIfNotExists/saveCompareAndSwap operations before returning an error.
     */
    public function __construct(
        private StorageInterface $storage,
        private int $limit,
        int $periodInSeconds,
        private int $storageTtlInSeconds = self::DEFAULT_TTL,
        private string $storagePrefix = self::ID_PREFIX,
        ClockInterface|null $timer = null,
        private int $maxCasAttempts = self::DEFAULT_MAX_CAS_ATTEMPTS,
    ) {
        if ($limit < 1) {
            throw new InvalidArgumentException('The limit must be a positive value.');
        }

        if ($periodInSeconds < 1) {
            throw new InvalidArgumentException('The period must be a positive value.');
        }

        $this->periodInMilliseconds = $periodInSeconds * self::MILLISECONDS_PER_SECOND;
        $this->timer = $timer ?: new SystemClock();
        $this->incrementIntervalInMilliseconds = $this->periodInMilliseconds / $this->limit;
    }

    /**
     * {@inheritdoc}
     */
    public function hit(string $id): CounterState
    {
        $attempts = 0;
        $isFailStoreUpdatedData = false;
        do {
            // Last increment time.
            // In GCRA it's known as arrival time.
            $lastIncrementTimeInMilliseconds = $this->timer->now()->format('U.u') * 1000;

            $lastStoredTheoreticalNextIncrementTime = $this->getLastStoredTheoreticalNextIncrementTime($id);

            $theoreticalNextIncrementTime = $this->calculateTheoreticalNextIncrementTime(
                $lastIncrementTimeInMilliseconds,
                $lastStoredTheoreticalNextIncrementTime
            );

            $remaining = $this->calculateRemaining($lastIncrementTimeInMilliseconds, $theoreticalNextIncrementTime);
            $resetAfter = $this->calculateResetAfter($theoreticalNextIncrementTime);

            if ($remaining === 0) {
                break;
            }

            $isStored = $this->storeTheoreticalNextIncrementTime(
                $id,
                $theoreticalNextIncrementTime,
                $lastStoredTheoreticalNextIncrementTime
            );
            if ($isStored) {
                break;
            }

            $attempts++;
            if ($attempts >= $this->maxCasAttempts) {
                $isFailStoreUpdatedData = true;
                break;
            }
        } while (true);

        return new CounterState($this->limit, $remaining, $resetAfter, $isFailStoreUpdatedData);
    }

    /**
     * @return float Theoretical increment time that would be expected from equally spaced increments at exactly rate
     * limit. In GCRA it's known as TAT, theoretical arrival time.
     */
    private function calculateTheoreticalNextIncrementTime(
        float $lastIncrementTimeInMilliseconds,
        ?float $storedTheoreticalNextIncrementTime
    ): float {
        return (
            $storedTheoreticalNextIncrementTime === null
                ? $lastIncrementTimeInMilliseconds
                : max($lastIncrementTimeInMilliseconds, $storedTheoreticalNextIncrementTime)
        ) + $this->incrementIntervalInMilliseconds;
    }

    /**
     * @return int The number of remaining requests in the current time period.
     */
    private function calculateRemaining(
        float $lastIncrementTimeInMilliseconds,
        float $theoreticalNextIncrementTime
    ): int {
        $incrementAllowedAt = $theoreticalNextIncrementTime - $this->periodInMilliseconds;

        $remainingTimeInMilliseconds = round($lastIncrementTimeInMilliseconds - $incrementAllowedAt);
        if ($remainingTimeInMilliseconds > 0) {
            return (int) ($remainingTimeInMilliseconds / $this->incrementIntervalInMilliseconds);
        }

        return 0;
    }

    private function getLastStoredTheoreticalNextIncrementTime(string $id): ?float
    {
        return $this->storage->get($this->getStorageKey($id));
    }

    private function storeTheoreticalNextIncrementTime(
        string $id,
        float $theoreticalNextIncrementTime,
        ?float $lastStoredTheoreticalNextIncrementTime
    ): bool {
        if ($lastStoredTheoreticalNextIncrementTime !== null) {
            return $this->storage->saveCompareAndSwap(
                $this->getStorageKey($id),
                $lastStoredTheoreticalNextIncrementTime,
                $theoreticalNextIncrementTime,
                $this->storageTtlInSeconds
            );
        }

        return $this->storage->saveIfNotExists(
            $this->getStorageKey($id),
            $theoreticalNextIncrementTime,
            $this->storageTtlInSeconds
        );
    }

    /**
     * @return int Timestamp to wait until the rate limit resets.
     */
    private function calculateResetAfter(float $theoreticalNextIncrementTime): int
    {
        return (int) ($theoreticalNextIncrementTime / self::MILLISECONDS_PER_SECOND);
    }

    /**
     * @return string Storage key used to store the next increment time.
     */
    private function getStorageKey(string $id): string
    {
        return $this->storagePrefix . $id;
    }
}
