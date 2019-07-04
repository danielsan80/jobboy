<?php

namespace JobBoy\Process\Domain\ProcessWorker;

use JobBoy\Process\Domain\Entity\Process;
use JobBoy\Process\Domain\ProcessStatus;
use JobBoy\Process\Domain\Repository\ProcessRepositoryInterface;

class ProcessWorker
{
    /** @var ProcessRepositoryInterface */
    protected $processRepository;

    public function __construct(
        ProcessRepositoryInterface $processRepository
    )
    {
        $this->processRepository = $processRepository;
    }


    public function work(): void
    {
        $this->lock();

        $types = [
            'handled',
            'failing',
            'ending',
            'running',
            'starting',
        ];

        foreach ($types as $type) {
            $process = $this->process($type);

            if ($process) {
                $this->iterate($process);
                $this->release();
                return;
            }
        }

        $this->release();
    }

    protected function lock()
    {
    }

    protected function release()
    {
    }


    protected function process(string $type): ?Process
    {
        if ($type == 'handled') {
            return $this->handled();
        }
        return $this->byStatus(ProcessStatus::fromScalar($type));
    }

    protected function handled(): ?Process
    {
        $processes = $this->processRepository->handled('0 seconds', 1);

        foreach ($processes as $process) {
            return $process;
        }

        return null;
    }

    protected function byStatus(ProcessStatus $status): ?Process
    {
        $processes = $this->processRepository->byStatus($status);

        foreach ($processes as $process) {
            return $process;
        }

        return null;
    }

}