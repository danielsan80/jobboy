<?php

namespace JobBoy\Process\Domain\MemoryLimit;

class NullMemoryLimit implements MemoryLimit
{

    public function get(): int
    {
        throw new \LogicException('Method not supported');
    }

    public function isExceeded(): bool
    {
        return false;
    }

}