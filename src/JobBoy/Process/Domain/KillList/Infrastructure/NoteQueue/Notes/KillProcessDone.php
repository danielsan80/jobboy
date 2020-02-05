<?php

namespace JobBoy\Process\Domain\KillList\Infrastructure\NoteQueue\Notes;

class KillProcessDone
{
    /** @var string */
    protected $processId;

    public function __construct(string $processId)
    {
        $this->processId = $processId;
    }

    public function processId()
    {
        return $this->processId;
    }

}