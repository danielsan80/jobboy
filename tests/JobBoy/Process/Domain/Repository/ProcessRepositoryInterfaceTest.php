<?php

namespace Tests\JobBoy\Process\Domain\Repository;

use Dan\FixtureHandler\FixtureHandler;
use JobBoy\Clock\Domain\Clock;
use JobBoy\Clock\Domain\Infrastructure\Carbon\CarbonTimeFactory;
use JobBoy\Process\Domain\Entity\Data\ProcessData;
use JobBoy\Process\Domain\Entity\Factory\ProcessFactory;
use JobBoy\Process\Domain\Entity\Id\ProcessId;
use JobBoy\Process\Domain\Entity\Process;
use JobBoy\Process\Domain\ProcessParameters;
use JobBoy\Process\Domain\ProcessStatus;
use JobBoy\Process\Domain\Repository\ProcessRepositoryInterface;
use PHPUnit\Framework\TestCase;

abstract class ProcessRepositoryInterfaceTest extends TestCase
{

    abstract protected function createRepository(): ProcessRepositoryInterface;

    abstract protected function createFactory(): ProcessFactory;

    protected function createFixtureHandler():FixtureHandler
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
    public function can_add_and_remove_a_process()
    {
        $repository = $this->createRepository();
        $factory = $this->createFactory();

        $id = '00000000-0000-0000-0000-000000000001';
        $process = $factory->create(new ProcessData([
            'id' => new ProcessId($id),
            'code' => 'a_job',
            'parameters' => new ProcessParameters(['a_key' => 'a_value'])
        ]));

        $this->assertCount(0, $repository->all());

        $repository->add($process);

        $this->assertCount(1, $repository->all());

        $fetchedProcess = $repository->byId(new ProcessId($id));

        $this->assertProcessEquals($process, $fetchedProcess);

        $processes = $repository->all();

        $this->assertProcessEquals($process, $processes[0]);


        $repository->remove($fetchedProcess);

        $this->assertCount(0, $repository->all());
    }


    /**
     * @test
     */
    public function all_is_sorted_by_updated_at()
    {
        $repository = $this->createRepository();
        $factory = $this->createFactory();

        $fh = $this->createFixtureHandler();

        $this->aFewMinutesAgo($fh);

        $process1 = $factory->create(new ProcessData([
            'id' => new ProcessId('00000000-0000-0000-0000-000000000001'),
            'code' => 'a_job',
            'parameters' => new ProcessParameters(['a_key' => 'a_value'])
        ]));

        $repository->add($process1);

        $this->aFewMinutesLater($fh);

        $process2 = $factory->create(new ProcessData([
            'id' => new ProcessId('00000000-0000-0000-0000-000000000002'),
            'code' => 'a_job',
            'parameters' => new ProcessParameters(['a_key' => 'a_value'])
        ]));

        $repository->add($process2);

        $processes = $repository->all();

        $this->assertProcessEquals($process1, $processes[0]);
        $this->assertProcessEquals($process2, $processes[1]);

        $this->aFewMinutesLater($fh);

        $process1->touch();

        $processes = $repository->all();

        $this->assertProcessEquals($process2, $processes[0]);
        $this->assertProcessEquals($process1, $processes[1]);

        $this->aFewMinutesLater($fh);

        $process2->touch();

        $processes = $repository->all(0,1);

        $this->assertCount(1, $processes);
        $this->assertProcessEquals($process1, $processes[0]);


        $fh->getRef('time')->unfreeze();

    }

    /**
     * @test
     */
    public function all_slicing()
    {
        $repository = $this->createRepository();
        $factory = $this->createFactory();


        $fh = $this->createFixtureHandler();

        $this->aFewMinutesAgo($fh);

        $process1 = $factory->create(new ProcessData([
            'id' => new ProcessId('00000000-0000-0000-0000-000000000001'),
            'code' => 'a_job',
            'parameters' => new ProcessParameters(['a_key' => 'a_value'])
        ]));

        $repository->add($process1);

        $this->aFewMinutesLater($fh);

        $process2 = $factory->create(new ProcessData([
            'id' => new ProcessId('00000000-0000-0000-0000-000000000002'),
            'code' => 'a_job',
            'parameters' => new ProcessParameters(['a_key' => 'a_value'])
        ]));

        $repository->add($process2);

        $processes = $repository->all();

        $this->assertCount(2, $processes);
        $this->assertProcessEquals($process1, $processes[0]);
        $this->assertProcessEquals($process2, $processes[1]);

        $processes = $repository->all(0,1);

        $this->assertCount(1, $processes);
        $this->assertProcessEquals($process1, $processes[0]);

        $processes = $repository->all(1,1);

        $this->assertCount(1, $processes);
        $this->assertProcessEquals($process2, $processes[0]);

        $processes = $repository->all(1);

        $this->assertCount(1, $processes);
        $this->assertProcessEquals($process2, $processes[0]);


        $fh->getRef('time')->unfreeze();
    }


    /**
     * @test
     */
    public function handled_method()
    {
        $repository = $this->createRepository();
        $factory = $this->createFactory();


        $fh = $this->createFixtureHandler();

        $this->aFewMinutesAgo($fh);

        $process1 = $factory->create(new ProcessData([
            'id' => new ProcessId('00000000-0000-0000-0000-000000000001'),
            'code' => 'a_job',
            'parameters' => new ProcessParameters(['a_key' => 'a_value'])
        ]));

        $repository->add($process1);

        $this->aFewMinutesLater($fh);

        $process2 = $factory->create(new ProcessData([
            'id' => new ProcessId('00000000-0000-0000-0000-000000000002'),
            'code' => 'a_job',
            'parameters' => new ProcessParameters(['a_key' => 'a_value'])
        ]));

        $repository->add($process2);

        $processes = $repository->handled();

        $this->assertCount(0, $processes);

        $this->aFewMinutesLater($fh);

        $process1->handle();
        $processes = $repository->handled();

        $this->assertCount(1, $processes);
        $this->assertProcessEquals($process1, $processes[0]);

        $this->aFewMinutesLater($fh);

        $process2->handle();
        $processes = $repository->handled();

        $this->assertCount(2, $processes);
        $this->assertProcessEquals($process1, $processes[0]);
        $this->assertProcessEquals($process2, $processes[1]);

        $this->aFewMinutesLater($fh);

        $process1->release();
        $processes = $repository->handled();

        $this->assertCount(1, $processes);
        $this->assertProcessEquals($process2, $processes[0]);

        $fh->getRef('time')->unfreeze();

    }

    /**
     * @test
     */
    public function byStatus_method()
    {
        $repository = $this->createRepository();
        $factory = $this->createFactory();


        $fh = $this->createFixtureHandler();

        $this->aFewMinutesAgo($fh);

        $process1 = $factory->create(new ProcessData([
            'id' => new ProcessId('00000000-0000-0000-0000-000000000001'),
            'code' => 'a_job',
            'parameters' => new ProcessParameters(['a_key' => 'a_value'])
        ]));

        $repository->add($process1);

        $this->aFewMinutesLater($fh);

        $process2 = $factory->create(new ProcessData([
            'id' => new ProcessId('00000000-0000-0000-0000-000000000002'),
            'code' => 'a_job',
            'parameters' => new ProcessParameters(['a_key' => 'a_value'])
        ]));

        $repository->add($process2);

        $processes = $repository->byStatus(ProcessStatus::starting());

        $this->assertCount(2, $processes);

        $this->aFewMinutesLater($fh);

        $process1->changeStatusToRunning();

        $processes = $repository->byStatus(ProcessStatus::starting());

        $this->assertCount(1, $processes);
        $this->assertProcessEquals($process2, $processes[0]);

        $processes = $repository->byStatus(ProcessStatus::running());

        $this->assertCount(1, $processes);
        $this->assertProcessEquals($process1, $processes[0]);

        $this->aFewMinutesLater($fh);

        $process1->changeStatusToEnding();

        $processes = $repository->byStatus(ProcessStatus::ending());

        $this->assertCount(1, $processes);
        $this->assertProcessEquals($process1, $processes[0]);

        $this->aFewMinutesLater($fh);

        $process1->changeStatusToCompleted();

        $processes = $repository->byStatus(ProcessStatus::completed());

        $this->assertCount(1, $processes);
        $this->assertProcessEquals($process1, $processes[0]);


        $this->aFewMinutesLater($fh);

        $process2->changeStatusToFailing();

        $processes = $repository->byStatus(ProcessStatus::failing());

        $this->assertCount(1, $processes);
        $this->assertProcessEquals($process2, $processes[0]);

        $process2->changeStatusToFailed();

        $processes = $repository->byStatus(ProcessStatus::failed());

        $this->assertCount(1, $processes);
        $this->assertProcessEquals($process2, $processes[0]);


        $fh->getRef('time')->unfreeze();

    }


    /**
     * @test
     */
    public function stale_method()
    {
        $repository = $this->createRepository();
        $factory = $this->createFactory();


        $fh = $this->createFixtureHandler();

        $this->aFewMinutesAgo($fh);

        $process1 = $factory->create(new ProcessData([
            'id' => new ProcessId('00000000-0000-0000-0000-000000000001'),
            'code' => 'a_job',
            'parameters' => new ProcessParameters(['a_key' => 'a_value'])
        ]));

        $repository->add($process1);

        $fh->getRef('time')->unfreeze();

        $process2 = $factory->create(new ProcessData([
            'id' => new ProcessId('00000000-0000-0000-0000-000000000002'),
            'code' => 'a_job',
            'parameters' => new ProcessParameters(['a_key' => 'a_value'])
        ]));

        $repository->add($process2);

        $processes = $repository->stale(new \DateTimeImmutable('-50 minutes'));

        $this->assertCount(1, $processes);

        $this->assertProcessEquals($process1, $processes[0]);


    }



    protected function assertProcessEquals(?Process $process1, ?Process $process2)
    {
        if ($process1 === null) {
            $this->assertNull($process2);
        }

        $this->assertTrue($process1->id()->equals($process2->id()));
        $this->assertEquals($process1->code(), $process2->code());
        $this->assertTrue($process1->parameters()->equals($process2->parameters()));
        $this->assertDateTimeEquals($process1->createdAt(), $process2->createdAt());
        $this->assertDateTimeEquals($process1->updatedAt(), $process2->updatedAt());
        $this->assertDateTimeEquals($process1->startedAt(), $process2->startedAt());
        $this->assertDateTimeEquals($process1->endedAt(), $process2->endedAt());
        $this->assertDateTimeEquals($process1->handledAt(), $process2->handledAt());
        $this->assertTrue($process1->status()->equals($process2->status()));
        $this->assertTrue($process1->store()->equals($process2->store()));

    }

    protected function assertDateTimeEquals(?\DateTimeImmutable $date1, ?\DateTimeImmutable $date2)
    {
        if ($date1 === null) {
            $this->assertNull($date2);
            return;
        }

        $this->assertEquals($date1->format(\DateTime::ISO8601), $date2->format(\DateTime::ISO8601));

    }

    protected $minutesAgo = 0;

    public function aFewMinutesAgo(FixtureHandler $fh)
    {
        $this->minutesAgo = 100;

        $fh->getRef('time')->freeze(sprintf('-%d minutes', $this->minutesAgo));
    }

    public function aFewMinutesLater(FixtureHandler $fh)
    {
        $this->minutesAgo -= 10;

        $fh->getRef('time')->freeze(sprintf('-%d minutes', $this->minutesAgo));
    }


}
