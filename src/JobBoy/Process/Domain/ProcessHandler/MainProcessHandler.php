<?php

namespace JobBoy\Process\Domain\ProcessHandler;

use JobBoy\Process\Domain\Entity\Id\ProcessId;
use JobBoy\Process\Domain\Repository\ProcessRepositoryInterface;

class MainProcessHandler
{

    /** @var ProcessHandlerRegistry  */
    protected $registry;
    /** @var ProcessRepositoryInterface */
    protected $processRepository;

    public function __construct(
        ProcessHandlerRegistry $registry,
        ProcessRepositoryInterface $processRepository
    )
    {
        $this->registry = $registry;
        $this->processRepository = $processRepository;
    }

    public function handle(ProcessId $id): void
    {
        $handler = $this->registry->get($id);
        $process = $this->processRepository->byId($id);

        $process->handle();
        $handler->handle($id);
        $process->release();
    }

}