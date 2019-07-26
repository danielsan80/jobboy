<?php

namespace JobBoy\Process\Application\Service;

use JobBoy\Process\Domain\Repository\Infrastructure\Util\ProcessRepositoryUtil;
use JobBoy\Process\Domain\Repository\ProcessRepositoryInterface;

class RemoveOldProcesses
{

    /** @var ProcessRepositoryInterface */
    protected $processRepository;

    public function __construct(
        ProcessRepositoryInterface $processRepository
    )
    {
        $this->processRepository = $processRepository;
    }

    public function execute(int $days = 90): void
    {

        $processes = $this->processRepository->stale(ProcessRepositoryUtil::aFewDaysAgo($days));

        foreach ($processes as $process) {
            $this->processRepository->remove($process);
        }
    }

}