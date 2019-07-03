<?php

namespace JobBoy\Process\Domain\Repository\Infrastructure\InMemory;

use JobBoy\Process\Domain\Entity\Process;

class ProcessUtil
{
    public static function slice(array $items, ?int $start = null, ?int $length = null): array
    {
        $start = $start !== null ? $start : 0;
        $items = array_slice($items, $start, $length);
        return $items;
    }

    public static function getUpdatedAtCompareFunction()
    {
        return function (Process $a, Process $b) {
            if ($a->updatedAt() == $b->updatedAt()) {
                return 0;
            }
            return ($a->updatedAt() < $b->updatedAt()) ? -1 : 1;
        };
    }

    public static function notImplemented()
    {
        throw new \LogicException('Not implemented yet');

    }

}