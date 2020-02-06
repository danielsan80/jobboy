<?php

namespace JobBoy\Process\Domain\NoteQueue;

interface NoteQueueResolver
{
    public function __invoke(NoteQueue $queue);
}