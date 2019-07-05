<?php

namespace Tests\JobBoy\Process\Domain\Repository\Infrastructure\Redis;

use JobBoy\Process\Domain\Entity\Factory\ProcessFactory;
use JobBoy\Process\Domain\Entity\Infrastructure\Redis\Process;
use JobBoy\Process\Domain\Repository\Infrastructure\Redis\ProcessRepository;
use JobBoy\Process\Domain\Repository\Infrastructure\Redis\RedisFactory;
use JobBoy\Process\Domain\Repository\ProcessRepositoryInterface;
use Ramsey\Uuid\Uuid;
use Tests\JobBoy\Process\Domain\Repository\ProcessRepositoryInterfaceTest;

class ProcessRepositoryTest extends ProcessRepositoryInterfaceTest
{

    protected function createRepository(): ProcessRepositoryInterface
    {
        $redisFactory = new RedisFactory('redis');
        $redis = $redisFactory->create();

        $id = Uuid::uuid4();

        return new ProcessRepository($redis, 'test.jobboy.processes.' . $id);
    }

    protected function createFactory(): ProcessFactory
    {
        return new ProcessFactory(Process::class);
    }
}
