<?php

namespace JobBoy\Process\Domain\IterationMaker;

use JobBoy\Process\Domain\Entity\Id\ProcessId;
use JobBoy\Process\Domain\Entity\Process;
use JobBoy\Process\Domain\Event\EventBusInterface;
use JobBoy\Process\Domain\Event\NullEventBus;
use JobBoy\Process\Domain\Events\Process\ProcessKilled;
use JobBoy\Process\Domain\IterationMaker\Events\IterationMakerLocked;
use JobBoy\Process\Domain\IterationMaker\Events\IterationMakerReleased;
use JobBoy\Process\Domain\IterationMaker\Events\NoProcessesToPickFound;
use JobBoy\Process\Domain\IterationMaker\Events\ProcessPicked;
use JobBoy\Process\Domain\IterationMaker\Exception\IteratingYetException;
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
    const LOCK_KEY = 'iteration-maker';

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
            throw new IteratingYetException();
        }
        $this->lock = $this->lockFactory->create(self::LOCK_KEY);
        if (!$this->lock->acquire()) {
            throw new IteratingYetException();
        };

        $this->eventBus->publish(new IterationMakerLocked());
    }

    protected function release(): void
    {
        if (!$this->lock) {
            throw new NotIteratingYetException();
        }
        $this->lock->release();
        $this->lock = null;

        $this->eventBus->publish(new IterationMakerReleased());
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
        $id = $this->killList->first();

        if (!$id) {
            return null;
        }

        try {
            $process = $this->processRepository->byId(ProcessId::fromScalar($id));
        } catch (\InvalidArgumentException $e) {
            $this->killList->remove($id);
            return null;
        }

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

            if ($this->killList->inList($id)) {
                $this->killList->remove($id);

                $process->kill();

                $this->eventBus->publish(new ProcessKilled($id));

                return new IterationResponse();
            }

            $response = $this->processIterator->iterate($id);
            return $response;

        } catch (\Throwable $e) {
            $process = $this->processRepository->byId($id);
            $process->setReport('reason', 'exception: ' . $e->getMessage());
            $process->changeStatusToFailing();
            throw $e;
        }
    }

}