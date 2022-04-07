<?php

namespace Tests\JobBoy\Process\Domain\PauseControl\Infrastructure\NoteQueue;

use JobBoy\Process\Domain\Lock\Infrastructure\Filesystem\LockFactory;
use JobBoy\Process\Domain\NoteQueue\Infrastructure\File\FileNoteQueueControl;
use JobBoy\Process\Domain\PauseControl\Infrastructure\NoteQueue\Notes\IsPaused;
use JobBoy\Process\Domain\PauseControl\Infrastructure\NoteQueue\Notes\PauseRequest;
use JobBoy\Process\Domain\PauseControl\Infrastructure\NoteQueue\Notes\UnpauseRequest;
use JobBoy\Process\Domain\PauseControl\Infrastructure\NoteQueue\ResolveRequestsResolver;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Uuid;

class PauseResolverTest extends TestCase
{


    /**
     * @test
     */
    public function it_works()
    {

        $lockFactory = new LockFactory();

        $queueControl = new FileNoteQueueControl($lockFactory, sys_get_temp_dir().'/pause-resolver-test/'.Uuid::uuid4());

        $queueControl->push(new PauseRequest());
        $queueControl->push(new UnpauseRequest());
        $queueControl->push(new PauseRequest());


        $pauseResolver = new ResolveRequestsResolver();
        $queueControl->resolve($pauseResolver);

        $this->assertEquals([new IsPaused()], $queueControl->get());


    }


}
