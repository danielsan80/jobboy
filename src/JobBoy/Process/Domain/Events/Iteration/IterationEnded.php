<?php

namespace JobBoy\Process\Domain\Events\Iteration;

class IterationEnded
{

    /** @var string */
    protected $processId;

    public function __construct(string $processId)
    {
        $this->processId = $processId;
    }

    /**
     * @return string
     */
    public function processId(): string
    {
        return $this->processId;
    }

}