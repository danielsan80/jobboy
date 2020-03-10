<?php

namespace JobBoy\Process\Domain\Events\Process;

class ProcessRemoved
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