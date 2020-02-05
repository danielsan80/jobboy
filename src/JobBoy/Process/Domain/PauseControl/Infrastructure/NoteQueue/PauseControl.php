<?php

namespace JobBoy\Process\Domain\PauseControl\Infrastructure\NoteQueue;

use JobBoy\Process\Domain\NoteQueue\NoteQueueControl;
use JobBoy\Process\Domain\PauseControl\Infrastructure\NoteQueue\Notes\Pause;
use JobBoy\Process\Domain\PauseControl\Infrastructure\NoteQueue\Notes\Unpause;
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
        $this->noteQueueControl->send(new Pause());
    }

    public function unpause(): void
    {
        $this->noteQueueControl->send(new Unpause());
    }

    public function isPaused(): bool
    {
        $pauseResolver = new PauseResolver();
        $this->noteQueueControl->resolve($pauseResolver);
        return $pauseResolver->isPaused();
    }
}