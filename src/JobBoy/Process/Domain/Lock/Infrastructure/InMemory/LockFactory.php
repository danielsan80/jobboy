<?php

namespace JobBoy\Process\Domain\Lock\Infrastructure\InMemory;

use JobBoy\Process\Domain\Lock\LockFactoryInterface;
use JobBoy\Process\Domain\Lock\LockInterface;

class LockFactory implements LockFactoryInterface
{
    protected $locks = [];

    public function create(string $name): LockInterface
    {
        if (!isset($this->locks[$name])) {
            $this->locks[$name] = new Lock();
        }

        return $this->locks[$name];
    }
}