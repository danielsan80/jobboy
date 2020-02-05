<?php

namespace JobBoy\Process\Domain\IterationMaker;

use JobBoy\Process\Application\Service\Exception\WorkRunningYetException;
use JobBoy\Process\Domain\Entity\Id\ProcessId;
use JobBoy\Process\Domain\Entity\Process;
use JobBoy\Process\Domain\Event\EventBusInterface;
use JobBoy\Process\Domain\Event\NullEventBus;
use JobBoy\Process\Domain\IterationMaker\Events\NoProcessesToPickFound;
use JobBoy\Process\Domain\IterationMaker\Events\ProcessManagementLocked;
use JobBoy\Process\Domain\IterationMaker\Events\ProcessManagementReleased;
use JobBoy\Process\Domain\IterationMaker\Events\ProcessPicked;
use JobBoy\Process\Domain\IterationMaker\Exception\NotIteratingYetException;
use JobBoy\Process\Domain\KillList\KillList;
use JobBoy\Process\Domain\KillList\NullKillList;
use JobBoy\Process\Domain\Lock\LockFactoryInterface;
use JobBoy\Process\Domain\Lock\LockInterface;
use JobBoy\Process\Domain\ProcessHandler\IterationResponse;
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
    /** @var KillList|null */
    protected $killList;
    /** @var EventBusInterface */
    protected $eventBus;

    /** @var LockInterface */
    protected $lock;

    public function __construct(
        ProcessRepositoryInterface $processRepository,
        LockFactoryInterface $lockFactory,
        ProcessIterator $processIterator,
        ?KillList $killList = null,
        ?EventBusInterface $eventBus = null
    )
    {
        if (!$killList) {
            $killList = new NullKillList();
        }

        if (!$eventBus) {
            $eventBus = new NullEventBus();
        }

        $this->processRepository = $processRepository;
        $this->lockFactory = $lockFactory;
        $this->processIterator = $processIterator;
        $this->eventBus = $eventBus;
        $this->killList = $killList;
    }


    public function work(): IterationResponse
    {
        $this->lock();

        $types = [
            'handled',
            'killed',
            'failing',
            'ending',
            'running',
            'starting',
        ];

        foreach ($types as $type) {
            $process = $this->process($type);

            if ($process) {
                $this->eventBus->publish(new ProcessPicked($process->id(), $process->code(), $type, $process->store()->toScalar()));
                $response = $this->iterate($process);
                $this->release();
                return $response;
            }
        }

        $this->release();
        $this->eventBus->publish(new NoProcessesToPickFound());
        return new IterationResponse(false);
    }

    protected function lock(): void
    {
        if ($this->lock) {
            throw new WorkRunningYetException();
        }
        $this->lock = $this->lockFactory->create(self::LOCK_KEY);
        if (!$this->lock->acquire()) {
            throw new WorkRunningYetException();
        };

        $this->eventBus->publish(new ProcessManagementLocked());
    }

    protected function release(): void
    {
        if (!$this->lock) {
            throw new NotIteratingYetException();
        }
        $this->lock->release();
        $this->lock = null;

        $this->eventBus->publish(new ProcessManagementReleased());
    }


    protected function process(string $type): ?Process
    {
        if ($type == 'handled') {
            return $this->handled();
        }

        if ($type == 'killed') {
            return $this->killed();
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

    protected function killed(): ?Process
    {
        $ids = $this->killList->all();

        if ($ids) {
            return null;
        }
        $id = array_unshift($ids);

        $process = $this->processRepository->byId(ProcessId::fromScalar($id));

        return $process;

    }

    protected function byStatus(ProcessStatus $status): ?Process
    {
        $processes = $this->processRepository->byStatus($status, 0, 1);

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