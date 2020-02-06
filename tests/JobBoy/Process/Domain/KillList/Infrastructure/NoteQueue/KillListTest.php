<?php

namespace Tests\JobBoy\Process\Domain\KillList\Infrastructure\NoteQueue;

use JobBoy\Process\Domain\Lock\Infrastructure\Symfony\LockFactory;
use JobBoy\Process\Domain\NoteQueue\Infrastructure\File\FileNoteQueueControl;
use PHPUnit\Framework\TestCase;

use JobBoy\Process\Domain\KillList\Infrastructure\NoteQueue\KillList;
use Ramsey\Uuid\Uuid;
use Tests\JobBoy\Test\UuidUtil;

class KillListTest extends TestCase
{


    /**
     * @test
     */
    public function it_works()
    {

        $lockFactory = new LockFactory();

        $queueControl = new FileNoteQueueControl($lockFactory, sys_get_temp_dir().'/kill-list-test/'.Uuid::uuid4());
        $killList = new KillList($queueControl);

        $this->assertEquals([], $killList->all());

        $killList->add(UuidUtil::uuid(1));
        $killList->add(UuidUtil::uuid(1));
        $killList->add(UuidUtil::uuid(2));

        $this->assertEquals([UuidUtil::uuid(1), UuidUtil::uuid(2)], $killList->all());

        $killList->remove(UuidUtil::uuid(1));
        $killList->add(UuidUtil::uuid(3));
        $killList->remove(UuidUtil::uuid(3));

        $this->assertEquals([UuidUtil::uuid(2)],$killList->all());



    }

}
