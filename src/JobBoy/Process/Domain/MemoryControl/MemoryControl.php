<?php
namespace JobBoy\Process\Domain\MemoryControl;

interface MemoryControl
{
    public function limit(): int;

    public function usage(): int;

    public function isLimitExceeded(): bool;


}