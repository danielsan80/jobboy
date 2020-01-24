<?php

namespace JobBoy\Process\Domain\MemoryLimit;

class Util
{
    static public function isMemoryLimitExceeded(int $limit):bool
    {
        return memory_get_peak_usage() > $limit;
    }

}