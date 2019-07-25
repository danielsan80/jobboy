<?php

namespace JobBoy\Process\Domain\IterationMaker;

use JobBoy\Process\Domain\Entity\Id\ProcessId;
use JobBoy\Process\Domain\ProcessHandler\IterationResponse;
use JobBoy\Process\Domain\ProcessHandler\ProcessHandlerRegistry;
use JobBoy\Process\Domain\Repository\ProcessRepositoryInterface;

class ProcessIterator
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

    public function iterate(ProcessId $id): IterationResponse
    {
        $handler = $this->registry->get($id);
        $process = $this->processRepository->byId($id);

        $process->handle();
        $response = $handler->handle($id);

        $process = $this->processRepository->byId($id);
        $process->release();

        return $response;
    }

}