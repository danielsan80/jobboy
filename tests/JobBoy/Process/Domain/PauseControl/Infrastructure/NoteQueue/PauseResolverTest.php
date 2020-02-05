<?php

namespace Tests\JobBoy\Process\Domain\PauseControl\Infrastructure\NoteQueue;

use JobBoy\Process\Domain\Lock\Infrastructure\Symfony\LockFactory;
use JobBoy\Process\Domain\NoteQueue\Infrastructure\File\FileNoteQueueControl;
use JobBoy\Process\Domain\PauseControl\Infrastructure\NoteQueue\Notes\Pause;
use JobBoy\Process\Domain\PauseControl\Infrastructure\NoteQueue\Notes\Unpause;
use JobBoy\Process\Domain\PauseControl\Infrastructure\NoteQueue\PauseResolver;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Uuid;
use Tests\JobBoy\Test\UuidUtil;

class PauseResolverTest extends TestCase
{


    /**
     * @test
     */
    public function it_works()
    {

        $lockFactory = new LockFactory();

        $queueControl = new FileNoteQueueControl($lockFactory, sys_get_temp_dir().'/pause-resolver-test/'.Uuid::uuid4());

        $queueControl->send(new Pause());
        $queueControl->send(new Unpause());
        $queueControl->send(new Pause());


        $pauseResolver = new PauseResolver();
        $queueControl->resolve($pauseResolver);

        $this->assertTrue($pauseResolver->isPaused());


    }


}
