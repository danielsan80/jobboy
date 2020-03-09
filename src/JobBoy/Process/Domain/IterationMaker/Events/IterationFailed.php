<?php

namespace JobBoy\Process\Domain\IterationMaker\Events;

class IterationFailed
{

    /** @var string */
    protected $processId;

    /** @var \Throwable */
    protected $exception;

    public function __construct(string $processId, \Throwable $e)
    {
        $this->processId = $processId;
        $this->exception = $e;
    }

    /**
     * @return string
     */
    public function processId(): string
    {
        return $this->processId;
    }

    /**
     * @return \Throwable
     */
    public function exception(): \Throwable
    {
        return $this->exception;
    }

}