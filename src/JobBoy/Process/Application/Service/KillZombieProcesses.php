<?php

namespace JobBoy\Process\Application\Service;

use JobBoy\Process\Domain\Entity\Process;
use JobBoy\Process\Domain\Repository\ProcessRepositoryInterface;

class KillZombieProcesses
{

    /** @var ProcessRepositoryInterface */
    protected $processRepository;
    /** @var string */
    protected $maxHandledTime;

    public function __construct(
        ProcessRepositoryInterface $processRepository,
        string $maxHandledTime = '20 minutes'
    )
    {
        $this->processRepository = $processRepository;
        $this->maxHandledTime = $maxHandledTime;
    }

    public function execute(): void
    {
        $processes = $this->processRepository->handled($this->maxHandledTime);

        /** @var Process $process */
        foreach ($processes as $process) {
            $process->changeStatusToFailed();
            $process->release();
        }
    }

}