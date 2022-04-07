<?php
declare(strict_types=1);

namespace JobBoy\Process\Domain\Lock\Infrastructure\Redis;

use JobBoy\Process\Domain\Lock\Infrastructure\Filesystem\Lock;
use JobBoy\Process\Domain\Lock\LockFactoryInterface;
use JobBoy\Process\Domain\Lock\LockInterface;
use JobBoy\Process\Domain\Lock\LockSpace;
use Symfony\Component\Lock\LockFactory as SymfonyLockFactory;
use Symfony\Component\Lock\Store\FlockStore;
use Symfony\Component\Lock\Store\RedisStore;

class LockFactory implements LockFactoryInterface
{
    /** @var \Redis */
    private $redis;
    /** @var LockSpace */
    protected $space;

    public function __construct(\Redis $redis, ?LockSpace $space = null)
    {
        if (!$space) {
            $space = new LockSpace();
        }

        $this->space = $space;
        $this->redis = $redis;
    }

    protected function factory(): SymfonyLockFactory
    {
        if (!$this->factory) {
            $store = new RedisStore($this->redis);

            $factory = new SymfonyLockFactory($store);
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