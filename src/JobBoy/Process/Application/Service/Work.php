<?php

namespace JobBoy\Process\Application\Service;

use Dan\Clock\Domain\Timer;
use JobBoy\Process\Domain\IterationMaker\Exception\IteratingYetException;
use JobBoy\Process\Domain\IterationMaker\IterationMaker;

class Work
{
    /** @var IterationMaker */
    protected $iterationMaker;

    public function __construct(
        IterationMaker $iterationMaker
    )
    {
        $this->iterationMaker = $iterationMaker;
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