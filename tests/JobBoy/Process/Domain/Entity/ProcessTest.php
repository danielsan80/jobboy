<?php

namespace Tests\JobBoy\Process\Domain\Entity;

use Dan\FixtureHandler\FixtureHandler;
use JobBoy\Clock\Domain\Clock;
use JobBoy\Clock\Domain\Infrastructure\Carbon\CarbonTimeFactory;
use JobBoy\Process\Domain\Entity\Data\ProcessData;
use JobBoy\Process\Domain\Entity\Id\ProcessId;
use JobBoy\Process\Domain\ProcessParameters;
use PHPUnit\Framework\TestCase;

use JobBoy\Process\Domain\Entity\Process;
use Tests\JobBoy\Test\UuidUtil;

class ProcessTest extends TestCase
{



    protected function createFixtureHandler(): FixtureHandler
    {
        $fh = new FixtureHandler();

        $timeFactory = new CarbonTimeFactory();
        Clock::setTimeFactory($timeFactory);

        $fh->setRef('time', $timeFactory);

        return $fh;
    }

    /**
     * @test
     */
    public function process_init()
    {

        $fh = $this->createFixtureHandler();

        $this->aFewMinutesAgo($fh);

        $t0 = Clock::createDateTimeImmutable('now');

        $process = Process::create(new ProcessData([
            'id' => new ProcessId(UuidUtil::uuid(1)),
            'code' => 'a_code',
            'parameters' => new ProcessParameters(['a_key' => 'a_value']),
        ]));

        $this->assertEquals(new ProcessId(UuidUtil::uuid(1)), $process->id());
        $this->assertEquals('a_code', $process->code());
        $this->assertEquals(['a_key' => 'a_value'], $process->parameters()->toScalar());
        $this->assertTrue($process->status()->isStarting());
        $this->assertEquals($t0, $process->createdAt());
        $this->assertEquals($t0, $process->updatedAt());
        $this->assertNull($process->startedAt());
        $this->assertNull($process->endedAt());
        $this->assertNull($process->handledAt());
        $this->assertNull($process->killedAt());
        $this->assertFalse($process->isHandled());
        $this->assertEmpty($process->store()->data());
    }

    /**
     * @test
     */
    public function process_handling()
    {

        $fh = $this->createFixtureHandler();

        $this->aFewMinutesAgo($fh);

        $t0 = Clock::createDateTimeImmutable('now');

        $process = Process::create(new ProcessData([
            'id' => new ProcessId(UuidUtil::uuid(1)),
            'code' => 'a_code',
            'parameters' => new ProcessParameters(['a_key' => 'a_value']),
        ]));


        $this->aFewMinutesLater($fh);
        $t1 = Clock::createDateTimeImmutable('now');


        $process->handle();

        $this->assertTrue($process->status()->isStarting());
        $this->assertEquals($t0, $process->createdAt());
        $this->assertEquals($t1, $process->updatedAt());
        $this->assertNull($process->startedAt());
        $this->assertNull($process->endedAt());
        $this->assertEquals($t1, $process->handledAt());
        $this->assertNull($process->killedAt());
        $this->assertTrue($process->isHandled());
        $this->assertEmpty($process->store()->data());


        $this->aFewMinutesLater($fh);
        $t2 = Clock::createDateTimeImmutable('now');

        $process->release();

        $this->assertTrue($process->status()->isStarting());
        $this->assertEquals($t0, $process->createdAt());
        $this->assertEquals($t2, $process->updatedAt());
        $this->assertNull($process->startedAt());
        $this->assertNull($process->endedAt());
        $this->assertNull($process->handledAt());
        $this->assertNull($process->killedAt());
        $this->assertFalse($process->isHandled());
        $this->assertEmpty($process->store()->data());


    }


    /**
     * @test
     */
    public function process_store()
    {

        $fh = $this->createFixtureHandler();

        $this->aFewMinutesAgo($fh);
        $t0 = Clock::createDateTimeImmutable('now');

        $process = Process::create(new ProcessData([
            'id' => new ProcessId(UuidUtil::uuid(1)),
            'code' => 'a_code',
            'parameters' => new ProcessParameters(['a_key' => 'a_value']),
        ]));

        $this->assertEmpty($process->store()->data());

        $this->aFewMinutesLater($fh);
        $t1 = Clock::createDateTimeImmutable('now');

        $this->assertFalse($process->has('city'));

        $process->handle();
        $process->set('city', 'Roma');

        $this->assertTrue($process->has('city'));
        $this->assertEquals('Roma', $process->get('city'));

        $this->assertTrue($process->status()->isStarting());
        $this->assertEquals($t0, $process->createdAt());
        $this->assertEquals($t1, $process->updatedAt());
        $this->assertNull($process->startedAt());
        $this->assertNull($process->endedAt());
        $this->assertEquals($t1, $process->handledAt());
        $this->assertNull($process->killedAt());
        $this->assertTrue($process->isHandled());
        $this->assertEquals(['city' => 'Roma'], $process->store()->data());


        $this->aFewMinutesLater($fh);
        $t2 = Clock::createDateTimeImmutable('now');


        $process->unset('city');

        $this->assertFalse($process->has('city'));
        $this->assertEquals('a default', $process->get('city', 'a default'));

        $process->release();



        $this->assertTrue($process->status()->isStarting());
        $this->assertEquals($t0, $process->createdAt());
        $this->assertEquals($t2, $process->updatedAt());
        $this->assertNull($process->startedAt());
        $this->assertNull($process->endedAt());
        $this->assertNull($process->handledAt());
        $this->assertNull($process->killedAt());
        $this->assertFalse($process->isHandled());
        $this->assertEmpty($process->store()->data());


    }



    /**
     * @test
     */
    public function process_success_scenario()
    {

        $fh = $this->createFixtureHandler();

        $this->aFewMinutesAgo($fh);
        $t0 = Clock::createDateTimeImmutable('now');

        $process = Process::create(new ProcessData([
            'id' => new ProcessId(UuidUtil::uuid(1)),
            'code' => 'a_code',
            'parameters' => new ProcessParameters(['a_key' => 'a_value']),
        ]));

        $this->aFewMinutesLater($fh);
        $t1 = Clock::createDateTimeImmutable('now');


        $process->handle();
        $process->changeStatusToRunning();

        $this->assertTrue($process->status()->isRunning());
        $this->assertEquals($t0, $process->createdAt());
        $this->assertEquals($t1, $process->updatedAt());
        $this->assertEquals($t1, $process->startedAt());
        $this->assertNull($process->endedAt());
        $this->assertEquals($t1, $process->handledAt());
        $this->assertNull($process->killedAt());
        $this->assertTrue($process->isHandled());
        $this->assertEmpty($process->store()->data());


        $this->aFewMinutesLater($fh);
        $t2 = Clock::createDateTimeImmutable('now');

        $process->changeStatusToEnding();


        $this->assertTrue($process->status()->isEnding());
        $this->assertEquals($t0, $process->createdAt());
        $this->assertEquals($t2, $process->updatedAt());
        $this->assertEquals($t1, $process->startedAt());
        $this->assertNull($process->endedAt());
        $this->assertEquals($t1, $process->handledAt());
        $this->assertNull($process->killedAt());
        $this->assertTrue($process->isHandled());
        $this->assertEmpty($process->store()->data());

        $this->aFewMinutesLater($fh);
        $t3 = Clock::createDateTimeImmutable('now');

        $process->changeStatusToCompleted();

        $this->assertTrue($process->status()->isCompleted());
        $this->assertEquals($t0, $process->createdAt());
        $this->assertEquals($t3, $process->updatedAt());
        $this->assertEquals($t1, $process->startedAt());
        $this->assertEquals($t3, $process->endedAt());
        $this->assertEquals($t1, $process->handledAt());
        $this->assertNull($process->killedAt());
        $this->assertTrue($process->isHandled());
        $this->assertEmpty($process->store()->data());

        $this->aFewMinutesLater($fh);
        $t4 = Clock::createDateTimeImmutable('now');

        $process->release();

        $this->assertTrue($process->status()->isCompleted());
        $this->assertEquals($t0, $process->createdAt());
        $this->assertEquals($t4, $process->updatedAt());
        $this->assertEquals($t1, $process->startedAt());
        $this->assertEquals($t3, $process->endedAt());
        $this->assertNull($process->handledAt());
        $this->assertNull($process->killedAt());
        $this->assertFalse($process->isHandled());
        $this->assertEmpty($process->store()->data());

    }


    /**
     * @test
     */
    public function process_fail_scenario()
    {

        $fh = $this->createFixtureHandler();

        $this->aFewMinutesAgo($fh);
        $t0 = Clock::createDateTimeImmutable('now');

        $process = Process::create(new ProcessData([
            'id' => new ProcessId(UuidUtil::uuid(1)),
            'code' => 'a_code',
            'parameters' => new ProcessParameters(['a_key' => 'a_value']),
        ]));

        $this->aFewMinutesLater($fh);
        $t1 = Clock::createDateTimeImmutable('now');


        $process->handle();
        $process->changeStatusToRunning();

        $this->aFewMinutesLater($fh);
        $t2 = Clock::createDateTimeImmutable('now');

        $process->changeStatusToFailing();

        $this->assertTrue($process->status()->isFailing());
        $this->assertEquals($t0, $process->createdAt());
        $this->assertEquals($t2, $process->updatedAt());
        $this->assertEquals($t1, $process->startedAt());
        $this->assertNull($process->endedAt());
        $this->assertEquals($t1, $process->handledAt());
        $this->assertNull($process->killedAt());
        $this->assertTrue($process->isHandled());
        $this->assertEmpty($process->store()->data());


        $this->aFewMinutesLater($fh);
        $t3 = Clock::createDateTimeImmutable('now');

        $process->changeStatusToFailed();

        $this->assertTrue($process->status()->isFailed());
        $this->assertEquals($t0, $process->createdAt());
        $this->assertEquals($t3, $process->updatedAt());
        $this->assertEquals($t1, $process->startedAt());
        $this->assertEquals($t3, $process->endedAt());
        $this->assertEquals($t1, $process->handledAt());
        $this->assertNull($process->killedAt());
        $this->assertTrue($process->isHandled());
        $this->assertEmpty($process->store()->data());

        $this->aFewMinutesLater($fh);
        $t4 = Clock::createDateTimeImmutable('now');

        $process->release();

        $this->assertTrue($process->status()->isFailed());
        $this->assertEquals($t0, $process->createdAt());
        $this->assertEquals($t4, $process->updatedAt());
        $this->assertEquals($t1, $process->startedAt());
        $this->assertEquals($t3, $process->endedAt());
        $this->assertNull($process->handledAt());
        $this->assertNull($process->killedAt());
        $this->assertFalse($process->isHandled());
        $this->assertEmpty($process->store()->data());

    }


    /**
     * @test
     */
    public function process_kill_starting_scenario()
    {

        $fh = $this->createFixtureHandler();

        $this->aFewMinutesAgo($fh);
        $t0 = Clock::createDateTimeImmutable('now');

        $process = Process::create(new ProcessData([
            'id' => new ProcessId(UuidUtil::uuid(1)),
            'code' => 'a_code',
            'parameters' => new ProcessParameters(['a_key' => 'a_value']),
        ]));

        $this->aFewMinutesLater($fh);
        $t1 = Clock::createDateTimeImmutable('now');


        $process->kill();

        $this->assertTrue($process->status()->isFailed());
        $this->assertEquals($t0, $process->createdAt());
        $this->assertEquals($t1, $process->updatedAt());
        $this->assertNull($process->startedAt());
        $this->assertNull($process->endedAt());
        $this->assertNull($process->handledAt());
        $this->assertEquals($t1, $process->killedAt());
        $this->assertFalse($process->isHandled());
        $this->assertEmpty($process->store()->data());


    }

    /**
     * @test
     */
    public function process_kill_running_scenario()
    {

        $fh = $this->createFixtureHandler();

        $this->aFewMinutesAgo($fh);
        $t0 = Clock::createDateTimeImmutable('now');

        $process = Process::create(new ProcessData([
            'id' => new ProcessId(UuidUtil::uuid(1)),
            'code' => 'a_code',
            'parameters' => new ProcessParameters(['a_key' => 'a_value']),
        ]));

        $this->aFewMinutesLater($fh);
        $t1 = Clock::createDateTimeImmutable('now');

        $process->changeStatusToRunning();

        $this->aFewMinutesLater($fh);
        $t2 = Clock::createDateTimeImmutable('now');

        $process->kill();

        $this->assertTrue($process->status()->isFailing());
        $this->assertEquals($t0, $process->createdAt());
        $this->assertEquals($t2, $process->updatedAt());
        $this->assertEquals($t1, $process->startedAt());
        $this->assertNull($process->endedAt());
        $this->assertNull($process->handledAt());
        $this->assertEquals($t2, $process->killedAt());
        $this->assertFalse($process->isHandled());
        $this->assertEmpty($process->store()->data());


        $this->aFewMinutesLater($fh);
        $t3 = Clock::createDateTimeImmutable('now');

        $process->changeStatusToFailed();

        $this->assertTrue($process->status()->isFailed());
        $this->assertEquals($t0, $process->createdAt());
        $this->assertEquals($t3, $process->updatedAt());
        $this->assertEquals($t1, $process->startedAt());
        $this->assertEquals($t3, $process->endedAt());
        $this->assertNull($process->handledAt());
        $this->assertEquals($t2, $process->killedAt());
        $this->assertFalse($process->isHandled());
        $this->assertEmpty($process->store()->data());

    }

    /**
     * @test
     */
    public function process_kill_failed_yet_scenario()
    {

        $fh = $this->createFixtureHandler();

        $this->aFewMinutesAgo($fh);
        $t0 = Clock::createDateTimeImmutable('now');

        $process = Process::create(new ProcessData([
            'id' => new ProcessId(UuidUtil::uuid(1)),
            'code' => 'a_code',
            'parameters' => new ProcessParameters(['a_key' => 'a_value']),
        ]));

        $this->aFewMinutesLater($fh);
        $t1 = Clock::createDateTimeImmutable('now');

        $process->changeStatusToRunning();

        $this->aFewMinutesLater($fh);
        $t2 = Clock::createDateTimeImmutable('now');

        $process->changeStatusToFailing();

        $this->aFewMinutesLater($fh);
        $t3 = Clock::createDateTimeImmutable('now');

        $process->kill();

        $this->assertTrue($process->status()->isFailing());
        $this->assertEquals($t0, $process->createdAt());
        $this->assertEquals($t2, $process->updatedAt());
        $this->assertEquals($t1, $process->startedAt());
        $this->assertNull($process->endedAt());
        $this->assertNull($process->handledAt());
        $this->assertNull($process->killedAt());
        $this->assertFalse($process->isHandled());
        $this->assertEmpty($process->store()->data());

        $this->aFewMinutesLater($fh);
        $t4 = Clock::createDateTimeImmutable('now');

        $process->changeStatusToFailed();

        $this->aFewMinutesLater($fh);
        $t5 = Clock::createDateTimeImmutable('now');

        $process->kill();

        $this->assertTrue($process->status()->isFailed());
        $this->assertEquals($t0, $process->createdAt());
        $this->assertEquals($t4, $process->updatedAt());
        $this->assertEquals($t1, $process->startedAt());
        $this->assertEquals($t4, $process->endedAt());
        $this->assertNull($process->handledAt());
        $this->assertNull($process->killedAt());
        $this->assertFalse($process->isHandled());
        $this->assertEmpty($process->store()->data());

    }

    /**
     * @test
     */
    public function process_kill_completed_yet_scenario()
    {

        $fh = $this->createFixtureHandler();

        $this->aFewMinutesAgo($fh);
        $t0 = Clock::createDateTimeImmutable('now');

        $process = Process::create(new ProcessData([
            'id' => new ProcessId(UuidUtil::uuid(1)),
            'code' => 'a_code',
            'parameters' => new ProcessParameters(['a_key' => 'a_value']),
        ]));

        $this->aFewMinutesLater($fh);
        $t1 = Clock::createDateTimeImmutable('now');

        $process->changeStatusToRunning();

        $this->aFewMinutesLater($fh);
        $t2 = Clock::createDateTimeImmutable('now');

        $process->changeStatusToEnding();

        $this->aFewMinutesLater($fh);
        $t3 = Clock::createDateTimeImmutable('now');

        $process->changeStatusToCompleted();

        $this->aFewMinutesLater($fh);
        $t4 = Clock::createDateTimeImmutable('now');

        $process->kill();

        $this->assertTrue($process->status()->isCompleted());
        $this->assertEquals($t0, $process->createdAt());
        $this->assertEquals($t3, $process->updatedAt());
        $this->assertEquals($t1, $process->startedAt());
        $this->assertEquals($t3, $process->endedAt());
        $this->assertNull($process->handledAt());
        $this->assertNull($process->killedAt());
        $this->assertFalse($process->isHandled());
        $this->assertEmpty($process->store()->data());

    }



    protected $minutesAgo = 0;

    protected function aFewMinutesAgo(FixtureHandler $fh, int $minutesAgo = 100)
    {
        $this->minutesAgo = $minutesAgo;

        $fh->getRef('time')->freeze(sprintf('-%d minutes', $this->minutesAgo));
    }

    protected function aFewMinutesLater(FixtureHandler $fh, $minutesLater = 10)
    {
        $this->minutesAgo -= $minutesLater;

        $fh->getRef('time')->freeze(sprintf('-%d minutes', $this->minutesAgo));
    }

}
