<?php

namespace JobBoy\Process\Domain\NoteQueue;

class NullNoteQueueControl implements NoteQueueControl
{

    public function push($note): void
    {
    }

    public function get(): array
    {
        return [];
    }

    public function resolve(callable $resolver)
    {
    }

}