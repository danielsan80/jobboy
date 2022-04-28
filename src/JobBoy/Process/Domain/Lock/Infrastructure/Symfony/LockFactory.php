<?php
declare(strict_types=1);

namespace JobBoy\Process\Domain\Lock\Infrastructure\Symfony;

use JobBoy\Process\Domain\Lock\Infrastructure\Filesystem\Lock;
use JobBoy\Process\Domain\Lock\LockFactoryInterface;
use JobBoy\Process\Domain\Lock\LockInterface;
use JobBoy\Process\Domain\Lock\LockSpace;
use Symfony\Component\Lock\Factory as SymfonyLockFactory;

class LockFactory implements LockFactoryInterface
{
    /** @var SymfonyLockFactory */
    private $symfonyLockFactory;
    /** @var LockSpace */
    private $space;


    public function __construct(SymfonyLockFactory $symfonyLockFactory, ?LockSpace $space = null)
    {
        if (!$space) {
            $space = new LockSpace();
        }

        $this->symfonyLockFactory = $symfonyLockFactory;
        $this->space = $space;
    }

    public function create(string $name): LockInterface
    {
        $lock = $this->symfonyLockFactory->createLock($this->space . '.' . $name);

        return new Lock($lock);
    }
}
