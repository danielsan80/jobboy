<?php

namespace JobBoy\Process\Domain\NoteQueue;

class NullNoteQueueControl implements NoteQueueControl
{

    public function send($note): void
    {
    }

    public function resolve(callable $resolver)
    {
    }
}