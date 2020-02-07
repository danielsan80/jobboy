<?php

namespace JobBoy\Process\Domain\MemoryControl;

class NullMemoryControl implements MemoryControl
{

    public function limit(): int
    {
        throw new \LogicException('Method not supported');
    }

    public function isLimitExceeded(): bool
    {
        return false;
    }

    public function usage(): int
    {
        throw new \LogicException('Method not supported');
    }
}