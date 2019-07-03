<?php

namespace JobBoy\Process\Application\Service;

use JobBoy\Process\Domain\Entity\Id\ProcessId;
use JobBoy\Process\Domain\Entity\Process;
use JobBoy\Process\Domain\Repository\ProcessRepositoryInterface;

class PickOneProcess
{
    /** @var ProcessRepositoryInterface */
    protected $processRepository;

    public function __construct(ProcessRepositoryInterface $processRepository)
    {
        $this->processRepository = $processRepository;
    }


    public function execute(): ?ProcessId
    {
        $evolvingProcesses = $this->processRepository->evolving(1);
        foreach ($evolvingProcesses as $process) {
            return $process->id();
        }

        $startingProcesses = $this->processRepository->starting(1);

        foreach ($startingProcesses as $process) {
            return $process->id();
        }
        return null;

    }

}