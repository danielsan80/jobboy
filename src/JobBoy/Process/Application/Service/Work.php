<?php

namespace JobBoy\Process\Application\Service;

use JobBoy\Clock\Domain\Timer;
use JobBoy\Process\Application\Service\Work\Events\IdleTimeStarted;
use JobBoy\Process\Application\Service\Work\Events\IteratingYetOccured;
use JobBoy\Process\Application\Service\Work\Events\MemoryLimitExceeded;
use JobBoy\Process\Application\Service\Work\Events\PauseTimeStarted;
use JobBoy\Process\Application\Service\Work\Events\Timedout;
use JobBoy\Process\Application\Service\Work\Events\WorkLocked;
use JobBoy\Process\Application\Service\Work\Events\WorkReleased;
use JobBoy\Process\Application\Service\Work\Exception\WorkIsNotRunningYetException;
use JobBoy\Process\Application\Service\Work\Exception\WorkRunningYetException;
use JobBoy\Process\Domain\Event\EventBusInterface;
use JobBoy\Process\Domain\Event\NullEventBus;
use JobBoy\Process\Domain\IterationMaker\Exception\IteratingYetException;
use JobBoy\Process\Domain\IterationMaker\IterationMaker;
use JobBoy\Process\Domain\Lock\LockFactoryInterface;
use JobBoy\Process\Domain\Lock\LockInterface;
use JobBoy\Process\Domain\MemoryControl\MemoryControl;
use JobBoy\Process\Domain\MemoryControl\NullMemoryControl;
use JobBoy\Process\Domain\PauseControl\NullPauseControl;
use JobBoy\Process\Domain\PauseControl\PauseControl;

class Work
{
    const LOCK_KEY = 'work';
    protected const GO_ON = true;
    protected const BREAK = false;

    protected const PAUSED = true;
    protected const NOT_PAUSED = false;


    /** @var IterationMaker */
    protected $iterationMaker;

    /** @var LockFactoryInterface */
    protected $lockFactory;

    /** @var EventBusInterface */
    protected $eventBus;

    /** @var MemoryControl|null */
    protected $memoryControl;

    /** @var NullPauseControl|PauseControl|null */
    protected $pauseControl;


    /** @var LockInterface */
    protected $lock;

    public function __construct(
        IterationMaker $iterationMaker,
        LockFactoryInterface $lockFactory,
        ?EventBusInterface $eventBus = null,
        ?MemoryControl $memoryControl = null,
        ?PauseControl $pauseControl = null
    )
    {
        if (!$eventBus) {
            $eventBus = new NullEventBus();
        }

        if (!$memoryControl) {
            $memoryControl = new NullMemoryControl();
        }

        if (!$pauseControl) {
            $pauseControl = new NullPauseControl();
        }
        $this->iterationMaker = $iterationMaker;
        $this->lockFactory = $lockFactory;
        $this->eventBus = $eventBus;
        $this->memoryControl = $memoryControl;
        $this->pauseControl = $pauseControl;
    }

    public function execute(int $timeout, int $idleTime): void
    {
        $this->lock();

        $timer = new Timer($timeout);

        while (true) {

            if ($this->makeIteration($idleTime) === self::BREAK) {
                break;
            };

            if ($this->checkMemoryLimit() === self::BREAK) {
                break;
            };

            if ($this->checkTimeout($timer, $timeout) === self::BREAK) {
                break;
            };

        }

        $this->release();
    }

    protected function checkIsPaused($idleTime): bool
    {
        $this->pauseControl->resolveRequests();
        if ($this->pauseControl->isPaused()) {
            $this->eventBus->publish(new PauseTimeStarted($idleTime));
            sleep($idleTime);

            return self::PAUSED;
        }
        return self::NOT_PAUSED;

    }

    protected function makeIteration(int $idleTime): bool
    {

        if ($this->checkIsPaused($idleTime)==self::PAUSED) {
            return self::GO_ON;
        }

        try {
            $response = $this->iterationMaker->work();
        } catch (IteratingYetException $e) {
            $this->eventBus->publish(new IteratingYetOccured());
            return self::BREAK;
        }

        if (!$response->hasWorked()) {
            $this->eventBus->publish(new IdleTimeStarted($idleTime));
            sleep($idleTime);
            return self::GO_ON;
        }

        return self::GO_ON;
    }

    protected function checkTimeout(Timer $timer, int $timeout): bool
    {
        if ($timer->isTimedout()) {
            $this->eventBus->publish(new TimedOut($timeout));
            return self::BREAK;
        }

        return self::GO_ON;
    }

    protected function checkMemoryLimit(): bool
    {
        if ($this->memoryControl->isLimitExceeded()) {
            $this->eventBus->publish(new MemoryLimitExceeded($this->memoryControl->usage()));
            return self::BREAK;
        }

        return self::GO_ON;
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