<?php

namespace JobBoy\Process\Domain\Repository\Infrastructure\Util;

use JobBoy\Process\Domain\Entity\Process;

use JobBoy\Clock\Domain\Clock;

class ProcessRepositoryUtil
{
    public static function sort(array $processes): array
    {
        usort($processes, self::byUpdatedAtAsc());

        return $processes;
    }

    public static function slice(array $processes, ?int $start = null, ?int $length = null): array
    {
        $start = $start !== null ? $start : 0;
        $processes = array_slice($processes, $start, $length);
        return $processes;
    }

    public static function byUpdatedAtAsc()
    {
        return function (Process $a, Process $b) {
            if ($a->updatedAt() == $b->updatedAt()) {
                return 0;
            }
            return ($a->updatedAt() < $b->updatedAt()) ? -1 : 1;
        };
    }

    public static function aFewDaysAgo(int $days): \DateTimeImmutable
    {
        return Clock::createDateTimeImmutable(sprintf('today - %d days', $days));
    }

    public static function notImplemented()
    {
        throw new \LogicException('Not implemented yet');

    }

}