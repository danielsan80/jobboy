<?php

namespace JobBoy\Process\Application\Service;

use JobBoy\Process\Application\DTO\Process;
use JobBoy\Process\Domain\Repository\ProcessRepositoryInterface;

class ListProcesses
{
    /** @var ProcessRepositoryInterface */
    protected $processRepository;

    public function __construct(
        ProcessRepositoryInterface $processRepository
    )
    {
        $this->processRepository = $processRepository;
    }

    public function execute(?int $start = null, ?int $length = null ): array
    {
        $processes = $this->processRepository->all($start, $length);

        $result = [];
        foreach ($processes as $process) {
            $result[] = new Process($process);
        }

        return $result;
    }
}