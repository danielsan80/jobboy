<?php

namespace Tests\JobBoy\Process\Application\Service;

use Dan\FixtureHandler\FixtureHandler;
use JobBoy\Clock\Domain\Clock;
use JobBoy\Clock\Domain\Infrastructure\Carbon\CarbonTimeFactory;
use JobBoy\Process\Application\Service\Events\IdleTimeStarted;
use JobBoy\Process\Application\Service\Events\Timedout;
use JobBoy\Process\Domain\Entity\Data\ProcessData;
use JobBoy\Process\Domain\Entity\Factory\ProcessFactory;
use JobBoy\Process\Domain\Event\Message\Message;
use JobBoy\Process\Domain\IterationMaker\Events\ProcessPicked;
use JobBoy\Process\Domain\IterationMaker\IterationMaker;
use JobBoy\Process\Domain\ProcessHandler\IterationResponse;
use JobBoy\Process\Domain\ProcessStatus;
use JobBoy\Process\Domain\Repository\Infrastructure\InMemory\ProcessRepository;
use PHPUnit\Framework\TestCase;

use JobBoy\Process\Application\Service\Work;
use Tests\JobBoy\Process\Test\Domain\Event\SpyEventBus;

class WorkTest extends TestCase
{


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
    public function it_works()
    {

        $fh = $this->createFixtureHandler();

        $processRepository = new ProcessRepository();
        $processFactory = new ProcessFactory();

        $this->aFewMinutesAgo($fh);

        $process = $processFactory->create(new ProcessData([
            'code' => 'job1'
        ]));
        $processRepository->add($process);

        $this->aFewMinutesLater($fh);

        $process = $processFactory->create(new ProcessData([
            'code' => 'job2'
        ]));
        $processRepository->add($process);

        $iterationMaker = \Mockery::mock(IterationMaker::class);
        $iterationMaker->shouldReceive('work')
            ->andReturnUsing(function() use ($processRepository) {
                $processes = array_merge(
                    $processRepository->byStatus(ProcessStatus::running()),
                    $processRepository->byStatus(ProcessStatus::starting())
                );
                if (!$processes) {
                    return new IterationResponse(false);
                }
                $process = $processes[0];
                switch (true) {
                    case $process->status()->isStarting():
                        $process->changeStatusToRunning();
                        break;
                    case $process->status()->isRunning():
                        $process->changeStatusToCompleted();
                        break;
                }

                return new IterationResponse(true);
            });

        $eventBus = new SpyEventBus();

        $service = new Work(
            $iterationMaker,
            $eventBus
        );

        $this->assertProcessRepositoryEquals([
            'job1' => 'starting',
            'job2' => 'starting',
        ], $processRepository);


        $this->aFewMinutesLater($fh);
        $service->execute(0,0);

        $this->assertProcessRepositoryEquals([
            'job2' => 'starting',
            'job1' => 'running',
        ], $processRepository);

        $this->aFewMinutesLater($fh);
        $service->execute(0,0);

        $this->assertProcessRepositoryEquals([
            'job2' => 'starting',
            'job1' => 'completed',
        ], $processRepository);

        $this->aFewMinutesLater($fh);
        $service->execute(0,0);

        $this->assertProcessRepositoryEquals([
            'job1' => 'completed',
            'job2' => 'running',
        ], $processRepository);

        $this->aFewMinutesLater($fh);
        $service->execute(0,0);

        $this->assertProcessRepositoryEquals([
            'job1' => 'completed',
            'job2' => 'completed',
        ], $processRepository);

        $this->aFewMinutesLater($fh);
        $service->execute(0,0);

        $this->assertProcessRepositoryEquals([
            'job1' => 'completed',
            'job2' => 'completed',
        ], $processRepository);


        $this->assertEventBusEquals([
            ['class' =>Timedout::class, 'text' => 'Timeout: {{seconds}} seconds', 'parameters' => ['seconds' =>0]],
            ['class' =>Timedout::class, 'text' => 'Timeout: {{seconds}} seconds', 'parameters' => ['seconds' =>0]],
            ['class' =>Timedout::class, 'text' => 'Timeout: {{seconds}} seconds', 'parameters' => ['seconds' =>0]],
            ['class' =>Timedout::class, 'text' => 'Timeout: {{seconds}} seconds', 'parameters' => ['seconds' =>0]],
            ['class' =>Timedout::class, 'text' => 'Timeout: {{seconds}} seconds', 'parameters' => ['seconds' =>0]],
            ['class' =>Timedout::class, 'text' => 'Timeout: {{seconds}} seconds', 'parameters' => ['seconds' =>0]],
        ], $eventBus);

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

    protected function assertProcessRepositoryEquals(array $expected, ProcessRepository $processRepository)
    {

        $processes = $processRepository->all();
        $this->assertCount(count($expected), $processes);

        $i = 0;
        foreach ($expected as $code => $status) {
            $this->assertEquals($code, $processes[$i]->code());
            $this->assertEquals($status, (string)$processes[$i]->status());
            $i++;
        }
    }

    protected function assertEventBusEquals(array $expected, SpyEventBus $eventBus)
    {
        $events = $eventBus->getSpiedEvents();
        TestCase::assertEquals(count($expected), count($events));

        foreach ($events as $i => $event) {
            TestCase::assertEquals($expected[$i], $event->toArray(), 'index: '.$i);
        }
    }



}
