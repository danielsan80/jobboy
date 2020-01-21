<?php

namespace JobBoy\Process\Application\Service;

use JobBoy\Clock\Domain\Timer;
use JobBoy\Process\Application\Service\Events\IdleTimeStarted;
use JobBoy\Process\Application\Service\Events\Timedout;
use JobBoy\Process\Application\Service\Events\WorkLocked;
use JobBoy\Process\Application\Service\Events\PauseTimeStarted;
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
use JobBoy\Process\Domain\PauseControl\NullPauseControl;
use JobBoy\Process\Domain\PauseControl\PauseControl;

class Work
{
    const LOCK_KEY = 'work';
    protected const CONTINUE = true;
    protected const BREAK = false;

    /** @var IterationMaker */
    protected $iterationMaker;

    /** @var LockFactoryInterface */
    protected $lockFactory;

    /** @var NullPauseControl|PauseControl|null */
    protected $pauseControl;

    /** @var EventBusInterface */
    protected $eventBus;

    /** @var LockInterface */
    protected $lock;

    public function __construct(
        IterationMaker $iterationMaker,
        LockFactoryInterface $lockFactory,
        ?PauseControl $pauseControl = null,
        ?EventBusInterface $eventBus = null
    )
    {
        if (!$pauseControl) {
            $pauseControl = new NullPauseControl();
        }
        if (!$eventBus) {
            $eventBus = new NullEventBus();
        }
        $this->iterationMaker = $iterationMaker;
        $this->lockFactory = $lockFactory;
        $this->pauseControl = $pauseControl;
        $this->eventBus = $eventBus;
    }

    public function execute(int $timeout, int $idleTime): void
    {
        $this->lock();

        $timer = new Timer($timeout);

        while (true) {

            if ($this->makeIteration($idleTime) === self::BREAK) {
                break;
            };

            if ($this->checkTimeout($timer, $timeout) === self::BREAK) {
                break;
            };
        }

        $this->release();
    }

    protected function makeIteration(int $idleTime): bool
    {
        if ($this->pauseControl->isPaused()) {
            $this->eventBus->publish(new PauseTimeStarted($idleTime));
            sleep($idleTime);

            return self::CONTINUE;
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
            return self::CONTINUE;
        }

        return self::CONTINUE;
    }

    protected function checkTimeout(Timer $timer, int $timeout): bool
    {
        if ($timer->isTimedout()) {
            $this->eventBus->publish(new TimedOut($timeout));
            return self::BREAK;
        }

        return self::CONTINUE;
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