<?php

namespace JobBoy\Process\Application\Service;

use JobBoy\Process\Domain\IterationMaker\IterationMaker;

class IterateOneProcess
{
    /** @var IterationMaker */
    protected $iterationMaker;

    public function __construct(
        IterationMaker $iterationMaker
    )
    {
        $this->iterationMaker = $iterationMaker;
    }

    public function execute(): void
    {
        $this->iterationMaker->work();
    }

}