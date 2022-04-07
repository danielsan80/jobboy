<?php

namespace Tests\JobBoy\Process\Domain\KillList\Infrastructure\NoteQueue;

use JobBoy\Process\Domain\KillList\Infrastructure\NoteQueue\KillResolver;
use JobBoy\Process\Domain\KillList\Infrastructure\NoteQueue\Notes\KillProcess;
use JobBoy\Process\Domain\KillList\Infrastructure\NoteQueue\Notes\KillProcessDone;
use JobBoy\Process\Domain\Lock\Infrastructure\Filesystem\LockFactory;
use JobBoy\Process\Domain\NoteQueue\Infrastructure\File\FileNoteQueueControl;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Uuid;
use Tests\JobBoy\Test\UuidUtil;

class KillResolverTest extends TestCase
{



    /**
     * @test
     */
    public function it_works()
    {

        $lockFactory = new LockFactory();

        $queueControl = new FileNoteQueueControl($lockFactory, sys_get_temp_dir().'/kill-resolver-test/'.Uuid::uuid4());

        $killResolver = new KillResolver();
        $queueControl->resolve($killResolver);
        $this->assertEquals([],$killResolver->list());

        $queueControl->push(new KillProcess(UuidUtil::uuid(1)));
        $queueControl->push(new KillProcess(UuidUtil::uuid(1)));
        $queueControl->push(new KillProcess(UuidUtil::uuid(2)));

        $killResolver = new KillResolver();
        $queueControl->resolve($killResolver);
        $this->assertEquals([UuidUtil::uuid(1), UuidUtil::uuid(2)],$killResolver->list());

        $queueControl->push(new KillProcessDone(UuidUtil::uuid(1)));
        $queueControl->push(new KillProcess(UuidUtil::uuid(3)));
        $queueControl->push(new KillProcessDone(UuidUtil::uuid(3)));

        $killResolver = new KillResolver();
        $queueControl->resolve($killResolver);
        $this->assertEquals([UuidUtil::uuid(2)],$killResolver->list());



    }


}
