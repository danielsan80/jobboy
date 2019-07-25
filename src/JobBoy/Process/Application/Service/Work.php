<?php

namespace JobBoy\Process\Application\Service;

use JobBoy\Clock\Domain\Timer;
use JobBoy\Process\Application\Service\Events\IdleTimeStarted;
use JobBoy\Process\Application\Service\Events\Timedout;
use JobBoy\Process\Domain\Event\EventBusInterface;
use JobBoy\Process\Domain\Event\NullEventBus;
use JobBoy\Process\Domain\IterationMaker\Exception\IteratingYetException;
use JobBoy\Process\Domain\IterationMaker\IterationMaker;

class Work
{
    /** @var IterationMaker */
    protected $iterationMaker;

    /** @var EventBusInterface */
    protected $eventBus;

    public function __construct(
        IterationMaker $iterationMaker,
        ?EventBusInterface $eventBus = null
    )
    {
        if (!$eventBus) {
            $eventBus = new NullEventBus();
        }
        $this->iterationMaker = $iterationMaker;
        $this->eventBus = $eventBus;
    }

    public function execute(int $timeout, int $idleTime): void
    {
        $timer = new Timer($timeout);

        do {
            try {
                $response = $this->iterationMaker->work();
                if (!$response->hasWorked()) {
                    if ($timer->isTimedout()) {
                        $this->eventBus->publish(new TimedOut($timeout));
                        break;
                    }
                    $this->eventBus->publish(new IdleTimeStarted($idleTime));
                    sleep($idleTime);
                }
            } catch (IteratingYetException $e) {
                if ($timer->isTimedout()) {
                    $this->eventBus->publish(new TimedOut($timeout));
                    break;
                }
                $this->eventBus->publish(new IdleTimeStarted($idleTime));
                sleep($idleTime);
            }
        } while (!$timer->isTimedout());
        $this->eventBus->publish(new TimedOut($timeout));
    }

}