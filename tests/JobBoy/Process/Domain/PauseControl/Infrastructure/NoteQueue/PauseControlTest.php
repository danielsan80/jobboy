<?php

namespace Tests\JobBoy\Process\Domain\PauseControl\Infrastructure\NoteQueue;

use JobBoy\Process\Domain\Lock\Infrastructure\Symfony\LockFactory;
use JobBoy\Process\Domain\NoteQueue\Infrastructure\File\FileNoteQueueControl;
use PHPUnit\Framework\TestCase;

use JobBoy\Process\Domain\PauseControl\Infrastructure\NoteQueue\PauseControl;
use Ramsey\Uuid\Uuid;

class PauseControlTest extends TestCase
{

    /**
     * @test
     */
    public function it_works()
    {

        $lockFactory = new LockFactory();

        $queueControl = new FileNoteQueueControl($lockFactory, sys_get_temp_dir().'/pause-control-test/'.Uuid::uuid4());
        $pauseControl = new PauseControl($queueControl);

        $this->assertFalse($pauseControl->isPaused());

        $pauseControl->pause();
        $pauseControl->pause();

        $this->assertTrue($pauseControl->isPaused());

        $pauseControl->unpause();
        $pauseControl->unpause();

        $this->assertFalse($pauseControl->isPaused());




    }

}
