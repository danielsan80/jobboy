<?php

namespace JobBoy\Process\Domain\Lock\Infrastructure\Symfony;

use JobBoy\Process\Domain\Lock\LockFactoryInterface;
use JobBoy\Process\Domain\Lock\LockInterface;
use Symfony\Component\Lock\Factory;
use Symfony\Component\Lock\Store\FlockStore;

class LockFactory implements LockFactoryInterface
{

    /** @var string|null */
    protected $locksDir;

    /** @var Factory|null */
    protected $factory;

    public function __construct($locksDir = null)
    {
        if (!$locksDir) {
            $locksDir = sys_get_temp_dir();
        }

        $this->locksDir = $locksDir;
    }

    protected function factory(): Factory
    {
        if (!$this->factory) {
            $store = new FlockStore($this->locksDir);

            $factory = new Factory($store);
            $this->factory = $factory;
        }

        return $this->factory;
    }

    public function create(string $name): LockInterface
    {

        $lock = $this->factory()->createLock($name);

        return new Lock($lock);
    }
}