<?php

namespace JobBoy\Process\Application\Service;

use JobBoy\Clock\Domain\Timer;
use JobBoy\Process\Application\Service\Events\IdleTimeStarted;
use JobBoy\Process\Application\Service\Events\Timedout;
use JobBoy\Process\Application\Service\Events\WorkLocked;
use JobBoy\Process\Application\Service\Events\WorkReleased;
use JobBoy\Process\Application\Service\Exception\WorkIsNotRunningYetException;
use JobBoy\Process\Application\Service\Exception\WorkRunningYetException;
use JobBoy\Process\Domain\Event\EventBusInterface;
use JobBoy\Process\Domain\Event\NullEventBus;
use JobBoy\Process\Domain\IterationMaker\Exception\IteratingYetException;
use JobBoy\Process\Domain\IterationMaker\IterationMaker;
use JobBoy\Process\Application\Service\Events\IteratingYetOccured;
use JobBoy\Process\Domain\Lock\LockFactoryInterface;
use JobBoy\Process\Domain\Lock\LockInterface;

class Work
{
    const LOCK_KEY = 'work';

    /** @var IterationMaker */
    protected $iterationMaker;

    /** @var LockFactoryInterface */
    protected $lockFactory;

    /** @var EventBusInterface */
    protected $eventBus;

    /** @var LockInterface */
    protected $lock;

    public function __construct(
        IterationMaker $iterationMaker,
        LockFactoryInterface $lockFactory,
        ?EventBusInterface $eventBus = null
    )
    {
        if (!$eventBus) {
            $eventBus = new NullEventBus();
        }
        $this->iterationMaker = $iterationMaker;
        $this->lockFactory = $lockFactory;
        $this->eventBus = $eventBus;
    }

    public function execute(int $timeout, int $idleTime): void
    {
        $this->lock();

        $timer = new Timer($timeout);

        while (true) {

            $continue = $this->makeIteration($timer, $timeout, $idleTime);
            if (!$continue) {
                break;
            };
        }

        $this->release();
    }

    protected function makeIteration(Timer $timer, int $timeout, int $idleTime): bool
    {
        try {
            $response = $this->iterationMaker->work();
            if (!$response->hasWorked() && !$timer->isTimedout()) {
                $this->eventBus->publish(new IdleTimeStarted($idleTime));
                sleep($idleTime);
            }
        } catch (IteratingYetException $e) {
            $this->eventBus->publish(new IteratingYetOccured());
            return false;
        }

        if ($timer->isTimedout()) {
            $this->eventBus->publish(new TimedOut($timeout));
            return false;
        }

        return true;
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

        $this->eventBus->publish(new WorkLocked());
    }

    protected function release(): void
    {
        if (!$this->lock) {
            throw new WorkIsNotRunningYetException();
        }
        $this->lock->release();
        $this->lock = null;

        $this->eventBus->publish(new WorkReleased());
    }

}