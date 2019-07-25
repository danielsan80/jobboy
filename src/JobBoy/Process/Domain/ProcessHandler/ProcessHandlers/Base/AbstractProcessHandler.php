<?php

namespace JobBoy\Process\Domain\ProcessHandler\ProcessHandlers\Base;

use JobBoy\Process\Domain\Entity\Id\ProcessId;
use JobBoy\Process\Domain\Entity\Process;
use JobBoy\Process\Domain\ProcessHandler\ProcessHandlerInterface;
use JobBoy\Process\Domain\Repository\ProcessRepositoryInterface;

/**
 * It's a generic base for all ProcessHandlers. The ProcessRepository is always needed.
 */
abstract class AbstractProcessHandler implements ProcessHandlerInterface
{

    /** @var ProcessRepositoryInterface */
    protected $processRepository;

    public function __construct(ProcessRepositoryInterface $processRepository)
    {
        $this->processRepository = $processRepository;
    }

    protected function process(ProcessId $id): ?Process
    {
        return $this->processRepository->byId($id);
    }

}