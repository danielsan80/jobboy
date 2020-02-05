<?php

namespace JobBoy\Process\Domain\PauseControl\Infrastructure\NoteQueue;

use JobBoy\Process\Domain\NoteQueue\NoteQueue;
use JobBoy\Process\Domain\NoteQueue\NoteQueueResolver;
use JobBoy\Process\Domain\PauseControl\Infrastructure\NoteQueue\Notes\Pause;
use JobBoy\Process\Domain\PauseControl\Infrastructure\NoteQueue\Notes\Unpause;

class PauseResolver implements NoteQueueResolver
{

    protected $paused = false;

    public function isPaused(): bool
    {
        return $this->paused;
    }

    public function __invoke(NoteQueue $queue) {
        $pause = null;
        foreach($queue->all() as $note) {
            if ($note instanceof Pause) {
                if (!$pause) {
                    $pause = $note;
                } else {
                    $queue->remove($note);
                }
            }

            if ($note instanceof Unpause) {
                if ($pause) {
                    $queue->remove($pause);
                    $pause = null;
                }
                $queue->remove($note);
            }
        }
        $this->paused = (bool)$pause;
    }
}