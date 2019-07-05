<?php

namespace JobBoy\Process\Application\Service;

use Dan\Clock\Domain\Timer;
use JobBoy\Process\Domain\ProcessIterator\Exception\IteratingYetException;
use JobBoy\Process\Domain\ProcessIterator\ProcessIterator;

class Work
{
    /** @var ProcessIterator */
    protected $processIterator;

    public function __construct(
        ProcessIterator $processIterator
    )
    {
        $this->processIterator = $processIterator;
    }

    public function execute(int $timeout, int $idleTime): void
    {
        $timer = new Timer($timeout);

        while (!$timer->isTimedout()) {
            try {
                $this->processIterator->work();
            } catch (IteratingYetException $e) {
                sleep($idleTime);
            }
        }
    }

}