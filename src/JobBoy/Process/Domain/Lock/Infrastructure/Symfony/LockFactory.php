<?php

namespace JobBoy\Process\Domain\Lock\Infrastructure\Symfony;

use JobBoy\Process\Domain\Lock\LockFactoryInterface;
use JobBoy\Process\Domain\Lock\LockInterface;
use JobBoy\Process\Domain\Lock\LockSpace;
use Symfony\Component\Lock\Factory;
use Symfony\Component\Lock\Store\FlockStore;

class LockFactory implements LockFactoryInterface
{
    const LOCKS = '/locks';

    /** @var string|null */
    protected $locksDir;
    /** @var boolean */
    protected $locksDirExists = false;

    /** @var LockSpace */
    protected $space;

    /** @var Factory|null */
    protected $factory;

    public function __construct($locksDir = null, ?LockSpace $space = null)
    {
        if (!$locksDir) {
            $locksDir = sys_get_temp_dir().self::LOCKS;
        }

        if (!$space) {
            $space = new LockSpace();
        }

        $this->locksDir = $locksDir;
        $this->space = $space;
    }

    protected function ensureLocksDirExists(): void
    {
        if ($this->locksDirExists){
            return;
        }
        if (!file_exists($this->locksDir)) {
            mkdir($this->locksDir,0777, true);
        }
        $this->locksDirExists = true;
    }

    protected function factory(): Factory
    {
        if (!$this->factory) {
            $this->ensureLocksDirExists();
            $store = new FlockStore($this->locksDir);

            $factory = new Factory($store);
            $this->factory = $factory;
        }

        return $this->factory;
    }

    public function create(string $name): LockInterface
    {

        $lock = $this->factory()->createLock($this->space.'.'.$name);

        return new Lock($lock);
    }
}