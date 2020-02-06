<?php

namespace JobBoy\Process\Domain\NoteQueue;

interface NoteQueueControl
{
    public function push($note): void;

    public function get(): array;

    public function resolve(callable $resolver);
}