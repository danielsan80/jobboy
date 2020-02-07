<?php

namespace JobBoy\Process\Domain\MemoryControl;

class Util
{
    static public function isMemoryLimitExceeded(int $limit): bool
    {

    }

    static public function bytesToString(int $bytes): string
    {
        $map = ['B', 'K', 'M', 'G', 'T'];

        foreach ($map as $i => $unit) {

            if ($bytes < pow(1024, $i + 1)) {
                $threshold = pow(1024, $i);
                return round($bytes / $threshold) . $unit;
            }
        }

        return (string)$bytes.'B';

    }

}