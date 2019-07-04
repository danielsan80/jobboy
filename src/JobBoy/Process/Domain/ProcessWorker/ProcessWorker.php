<?php

namespace JobBoy\Process\Domain\ProcessWorker;

use JobBoy\Process\Domain\Entity\Process;
use JobBoy\Process\Domain\Lock\LockFactoryInterface;
use JobBoy\Process\Domain\Lock\LockInterface;
use JobBoy\Process\Domain\ProcessHandler\MainProcessHandler;
use JobBoy\Process\Domain\ProcessStatus;
use JobBoy\Process\Domain\ProcessWorker\Exception\NotWorkingYetException;
use JobBoy\Process\Domain\ProcessWorker\Exception\WorkingYetException;
use JobBoy\Process\Domain\Repository\ProcessRepositoryInterface;

class ProcessWorker
{
    const PROCESS_MANAGEMENT = 'process-management';

    /** @var ProcessRepositoryInterface */
    protected $processRepository;
    /** @var LockFactoryInterface */
    protected $lockFactory;
    /** @var MainProcessHandler */
    protected $mainProcessHandler;

    /** @var LockInterface */
    protected $lock;

    public function __construct(
        ProcessRepositoryInterface $processRepository,
        LockFactoryInterface $lockFactory,
        MainProcessHandler $mainProcessHandler
    )
    {
        $this->processRepository = $processRepository;
        $this->lockFactory = $lockFactory;
        $this->mainProcessHandler = $mainProcessHandler;
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

    protected function lock(): void
    {
        if ($this->lock) {
            throw new WorkingYetException();
        }
        $this->lock = $this->lockFactory->create(self::PROCESS_MANAGEMENT);
        if (!$this->lock->acquire()) {
            throw new WorkingYetException();
        };
    }

    protected function release(): void
    {
        if (!$this->lock) {
            throw new NotWorkingYetException();
        }
        $this->lock->release();
        $this->lock = null;
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

    protected function iterate(?Process $process): void
    {
        $id = $process->id();
        try {
            $this->mainProcessHandler->handle($id);
        } catch (\Exception $e) {
            $process = $this->processRepository->byId($id);
            $process->set('exception', $e->getMessage());
            $process->changeStatusToFailing();
            $process->release();
        }
    }

}