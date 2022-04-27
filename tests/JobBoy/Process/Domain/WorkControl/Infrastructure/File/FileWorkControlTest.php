<?php

namespace Tests\JobBoy\Process\Domain\WorkControl\Infrastructure\File;

use JobBoy\Process\Domain\Lock\Infrastructure\Filesystem\LockFactory;
use JobBoy\Process\Domain\NoteQueue\Infrastructure\File\FileNoteQueueControl;
use PHPUnit\Framework\TestCase;

class FileWorkControlTest extends TestCase
{

    /**
     * @test
     */
    public function it_works()
    {
        $this->markTestIncomplete();

        $lockFactory = new LockFactory();

        $workControl = new FileNoteQueueControl($lockFactory);


//        $workControl->resolve(function(CommandQueue $queue) {
//            foreach($queue->all() as $command) {
//                if ($command instanceof KillProcess) {
//                    $queue->remove($command);
//                    $processId = $command->processId();
//
//                    $process = $processRepository->byId();
//                    if (!$process) {
//                        continue;
//                    }
//
//                    $process->set('reason', 'killed by the user');
//                    if ($process->isStarting()) {
//                        $process->changeStatusToFailed();
//                    } else {
//                        $process->changeStatusToFailing();
//                    }
//                }
//            }
//        });

    }

}
