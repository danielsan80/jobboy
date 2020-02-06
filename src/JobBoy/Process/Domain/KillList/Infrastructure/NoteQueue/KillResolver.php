<?php

namespace JobBoy\Process\Domain\KillList\Infrastructure\NoteQueue;

use JobBoy\Process\Domain\KillList\Infrastructure\NoteQueue\Notes\KillProcess;
use JobBoy\Process\Domain\KillList\Infrastructure\NoteQueue\Notes\KillProcessDone;
use JobBoy\Process\Domain\NoteQueue\NoteQueue;
use JobBoy\Process\Domain\NoteQueue\NoteQueueResolver;

class KillResolver implements NoteQueueResolver
{

    protected $list = [];

    public function list(): array
    {
        return $this->list;
    }

    public function __invoke(NoteQueue $queue)
    {
        $kills = [];
        foreach ($queue->all() as $note) {
            if ($note instanceof KillProcess) {

                if (!isset($kills[$note->processId()])) {
                    $kills[$note->processId()] = $note;
                } else {
                    $queue->remove($note);
                }
            }

            if ($note instanceof KillProcessDone) {
                if (isset($kills[$note->processId()])) {
                    $queue->remove($kills[$note->processId()]);
                    unset($kills[$note->processId()]);
                }
                $queue->remove($note);
            }
        }
        $this->list = array_keys($kills);
    }
}