<?php

namespace JobBoy\Process\Domain\NoteQueue;

interface NoteQueueControl
{
    public function send($note): void;

    public function resolve(callable $resolver);
}