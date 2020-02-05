<?php

namespace JobBoy\Process\Domain\KillList\Infrastructure\NoteQueue;

use JobBoy\Process\Domain\KillList\Infrastructure\NoteQueue\Notes\KillProcess;
use JobBoy\Process\Domain\KillList\Infrastructure\NoteQueue\Notes\KillProcessDone;
use JobBoy\Process\Domain\KillList\KillList as KillListInterface;
use JobBoy\Process\Domain\NoteQueue\NoteQueueControl;

class KillList implements KillListInterface
{
    /** @var NoteQueueControl */
    protected $noteQueueControl;

    public function __construct(NoteQueueControl $noteQueueControl)
    {
        $this->noteQueueControl = $noteQueueControl;
    }


    public function kill(string $processId): void
    {
        $this->noteQueueControl->send(new KillProcess($processId));
    }

    public function done(string $processId): void
    {
        $this->noteQueueControl->send(new KillProcessDone($processId));
    }

    public function all(): array
    {
        $resolver = new KillResolver();
        $this->noteQueueControl->resolve($resolver);

        return $resolver->list();
    }

    public function toBeKilled(string $processId): bool
    {
        return in_array($processId, $this->all());
    }
}