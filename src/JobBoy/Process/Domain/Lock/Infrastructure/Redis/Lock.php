<?php
declare(strict_types=1);

namespace JobBoy\Process\Domain\Lock\Infrastructure\Redis;

use JobBoy\Process\Domain\Lock\LockInterface;
use Symfony\Component\Lock\LockInterface as SymfonyLock;

class Lock implements LockInterface
{
    /** @var SymfonyLock */
    protected $lock;

    public function __construct(SymfonyLock $lock)
    {
        $this->lock = $lock;
    }

    public function acquire(): bool
    {
        return $this->lock->acquire();
    }

    public function release(): void
    {
        $this->lock->release();
    }
}
