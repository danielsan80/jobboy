<?php

namespace Tests\JobBoy\Process\Domain\Repository\Infrastructure\InMemory;

use JobBoy\Process\Domain\Entity\Factory\ProcessFactory;
use JobBoy\Process\Domain\Repository\Infrastructure\InMemory\ProcessRepository;
use JobBoy\Process\Domain\Repository\ProcessRepositoryInterface;
use Tests\JobBoy\Process\Domain\Repository\ProcessRepositoryInterfaceTest;

class ProcessRepositoryTest extends ProcessRepositoryInterfaceTest
{

    protected function createRepository(): ProcessRepositoryInterface
    {
        return new ProcessRepository();
    }

    protected function createFactory(): ProcessFactory
    {
        return new ProcessFactory();
    }
}
