<?php

namespace JobBoy\Process\Domain\PauseControl\Infrastructure\NoteQueue;

use JobBoy\Process\Domain\NoteQueue\NoteQueueControl;
use JobBoy\Process\Domain\PauseControl\Infrastructure\NoteQueue\Notes\IsPaused;
use JobBoy\Process\Domain\PauseControl\Infrastructure\NoteQueue\Notes\PauseRequest;
use JobBoy\Process\Domain\PauseControl\Infrastructure\NoteQueue\Notes\UnpauseRequest;
use JobBoy\Process\Domain\PauseControl\PauseControl as PauseControlInterface;

class PauseControl implements PauseControlInterface
{
    /** @var NoteQueueControl */
    protected $noteQueueControl;

    public function __construct(NoteQueueControl $noteQueueControl)
    {
        $this->noteQueueControl = $noteQueueControl;
    }

    public function pause(): void
    {
        $this->noteQueueControl->push(new PauseRequest());
    }

    public function unpause(): void
    {
        $this->noteQueueControl->push(new UnpauseRequest());
    }

    public function isPaused(): bool
    {
        foreach ($this->noteQueueControl->get() as $note) {
            if ($note instanceof IsPaused) {
                return true;
            }
        }

        return false;
    }

    public function resolveRequests(): void
    {
        $resolver = new ResolveRequestsResolver();
        $this->noteQueueControl->resolve($resolver);
    }
}