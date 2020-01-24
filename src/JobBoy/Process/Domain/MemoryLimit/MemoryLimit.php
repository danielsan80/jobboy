<?php
namespace JobBoy\Process\Domain\MemoryLimit;

interface MemoryLimit
{
    public function get(): int;

    public function isExceeded(): bool;

}