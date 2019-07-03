<?php

namespace JobBoy\Process\Application\Service;

use JobBoy\Process\Domain\Entity\Id\ProcessId;
use JobBoy\Process\Domain\Repository\ProcessRepositoryInterface;

class ExecuteProcess
{

    /** @var ProcessRepositoryInterface */
    protected $processRepository;
    /** @var MainProcessHandler */
    protected $mainProcessHandler;

    public function __construct(
        ProcessRepositoryInterface $processRepository,
        MainProcessHandler $mainProcessHandler
    )
    {
        $this->processRepository = $processRepository;
        $this->mainProcessHandler = $mainProcessHandler;
    }

    public function execute(ProcessId $id): void
    {
        $process = $this->processRepository->byId($id);
        while ($process->status()->isActive()) {
            $this->mainProcessHandler->handle($id);
            $process = $this->processRepository->byId($id);
        }
    }

}