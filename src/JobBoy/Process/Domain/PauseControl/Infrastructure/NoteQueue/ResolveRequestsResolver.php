<?php

namespace JobBoy\Process\Domain\PauseControl\Infrastructure\NoteQueue;

use JobBoy\Process\Domain\NoteQueue\NoteQueue;
use JobBoy\Process\Domain\NoteQueue\NoteQueueResolver;
use JobBoy\Process\Domain\PauseControl\Infrastructure\NoteQueue\Notes\IsPaused;
use JobBoy\Process\Domain\PauseControl\Infrastructure\NoteQueue\Notes\PauseRequest;
use JobBoy\Process\Domain\PauseControl\Infrastructure\NoteQueue\Notes\UnpauseRequest;

class ResolveRequestsResolver implements NoteQueueResolver
{

    public function __invoke(NoteQueue $queue) {
        $isPaused = null;
        foreach($queue->all() as $note) {
            if ($note instanceof IsPaused) {
                if (!$isPaused) {
                    $isPaused = $note;
                }
                $queue->remove($note);
            }

            if ($note instanceof PauseRequest) {
                if (!$isPaused) {
                    $isPaused = new IsPaused();
                }

                $queue->remove($note);
            }

            if ($note instanceof UnpauseRequest) {
                if ($isPaused) {
                    $isPaused = null;
                }
                $queue->remove($note);
            }
        }

        if ($isPaused) {
            $queue->add($isPaused);
        }
    }
}