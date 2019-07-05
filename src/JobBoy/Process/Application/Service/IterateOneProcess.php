<?php

namespace JobBoy\Process\Application\Service;

use Dan\Clock\Domain\Timer;
use JobBoy\Process\Domain\ProcessIterator\Exception\IteratingYetException;
use JobBoy\Process\Domain\ProcessIterator\ProcessIterator;

class IterateOneProcess
{
    /** @var ProcessIterator */
    protected $processIterator;

    public function __construct(
        ProcessIterator $processIterator
    )
    {
        $this->processIterator = $processIterator;
    }

    public function execute(): void
    {
        $this->processIterator->work();
    }

}