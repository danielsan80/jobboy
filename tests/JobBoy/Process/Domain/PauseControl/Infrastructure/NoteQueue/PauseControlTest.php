<?php

namespace Tests\JobBoy\Process\Domain\PauseControl\Infrastructure\NoteQueue;

use JobBoy\Process\Domain\Lock\Infrastructure\Filesystem\LockFactory;
use JobBoy\Process\Domain\NoteQueue\Infrastructure\File\FileNoteQueueControl;
use JobBoy\Process\Domain\PauseControl\Infrastructure\NoteQueue\PauseControl;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Uuid;

class PauseControlTest extends TestCase
{

    /**
     * @test
     */
    public function it_works()
    {

        $lockFactory = new LockFactory();

        $queueControl = new FileNoteQueueControl($lockFactory, sys_get_temp_dir() . '/pause-control-test/' . Uuid::uuid4());
        $pauseControl = new PauseControl($queueControl);

        $pauseControl->resolveRequests();
        $this->assertFalse($pauseControl->isPaused());

        $pauseControl->pause();
        $pauseControl->pause();

        $pauseControl->resolveRequests();
        $this->assertTrue($pauseControl->isPaused());

        $pauseControl->unpause();
        $pauseControl->unpause();

        $pauseControl->resolveRequests();
        $this->assertFalse($pauseControl->isPaused());


    }

}
