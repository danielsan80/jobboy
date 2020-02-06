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


    public function add(string $processId): void
    {
        $this->noteQueueControl->send(new KillProcess($processId));
    }

    public function remove(string $processId): void
    {
        $this->noteQueueControl->send(new KillProcessDone($processId));
    }

    public function first(): ?string
    {
        $all = $this->all();

        if (!$all) {
            return null;
        }

        return array_shift($all);
    }

    public function all(): array
    {
        $resolver = new KillResolver();
        $this->noteQueueControl->resolve($resolver);

        return $resolver->list();
    }

    public function inList(string $processId): bool
    {
        return in_array($processId, $this->all());
    }

}