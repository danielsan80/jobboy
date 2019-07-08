<?php

namespace JobBoy\Process\Domain\IterationMaker;

use JobBoy\Process\Domain\Entity\Process;
use JobBoy\Process\Domain\IterationMaker\Exception\IteratingYetException;
use JobBoy\Process\Domain\IterationMaker\Exception\NotIteratingYetException;
use JobBoy\Process\Domain\Lock\LockFactoryInterface;
use JobBoy\Process\Domain\Lock\LockInterface;
use JobBoy\Process\Domain\ProcessIterator\IterationResponse;
use JobBoy\Process\Domain\ProcessIterator\ProcessIterator;
use JobBoy\Process\Domain\ProcessStatus;
use JobBoy\Process\Domain\Repository\ProcessRepositoryInterface;

class IterationMaker
{
    const LOCK_KEY = 'process-management';

    /** @var ProcessRepositoryInterface */
    protected $processRepository;
    /** @var LockFactoryInterface */
    protected $lockFactory;
    /** @var ProcessIterator */
    protected $processIterator;

    /** @var LockInterface */
    protected $lock;

    public function __construct(
        ProcessRepositoryInterface $processRepository,
        LockFactoryInterface $lockFactory,
        ProcessIterator $processIterator
    )
    {
        $this->processRepository = $processRepository;
        $this->lockFactory = $lockFactory;
        $this->processIterator = $processIterator;
    }


    public function work(): IterationResponse
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
                $response = $this->iterate($process);
                $this->release();
                return $response;
            }
        }

        $this->release();
        return new IterationResponse(false);
    }

    protected function lock(): void
    {
        if ($this->lock) {
            throw new IteratingYetException();
        }
        $this->lock = $this->lockFactory->create(self::LOCK_KEY);
        if (!$this->lock->acquire()) {
            throw new IteratingYetException();
        };
    }

    protected function release(): void
    {
        if (!$this->lock) {
            throw new NotIteratingYetException();
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
        $processes = $this->processRepository->handled(0, 1);

        foreach ($processes as $process) {
            return $process;
        }

        return null;
    }

    protected function byStatus(ProcessStatus $status): ?Process
    {
        $processes = $this->processRepository->byStatus($status,0,1);

        foreach ($processes as $process) {
            return $process;
        }

        return null;
    }

    protected function iterate(?Process $process): IterationResponse
    {
        $id = $process->id();
        try {
            $response = $this->processIterator->iterate($id);
            return $response;

        } catch (\Throwable $e) {
            $process = $this->processRepository->byId($id);
            $process->set('exception', $e->getMessage());
            $process->changeStatusToFailing();
            $process->release();
            throw $e;
        }
    }

}