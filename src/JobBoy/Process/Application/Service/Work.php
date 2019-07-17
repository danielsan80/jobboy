<?php

namespace JobBoy\Process\Application\Service;

use JobBoy\Clock\Domain\Timer;
use JobBoy\Process\Domain\Event\EventBusInterface;
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
        EventBusInterface $eventBus
    )
    {
        $this->iterationMaker = $iterationMaker;
        $this->eventBus = $eventBus;
    }

    public function execute(int $timeout, int $idleTime): void
    {
        $timer = new Timer($timeout);

        while (!$timer->isTimedout()) {
            try {
                $response = $this->iterationMaker->work();
                if (!$response->hasWorked()) {
                    sleep($idleTime);
                }
            } catch (IteratingYetException $e) {
                sleep($idleTime);
            }
        }
    }

}