<?php

namespace JobBoy\Process\Domain\Lock\Infrastructure\InMemory;

use JobBoy\Process\Domain\Lock\LockInterface;

class Lock implements LockInterface
{
    protected $locked = false;

    public function acquire(): bool
    {
        if ($this->locked) {
            return false;
        }

        $this->locked = true;

        return true;
    }

    public function release(): void
    {
        if (!$this->locked) {
            throw new \LogicException('This lock is not acquired yet');
        }
        $this->locked = false;
    }
}