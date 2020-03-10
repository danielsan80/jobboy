<?php

namespace Tests\JobBoy\Process\Application\Service;

use Dan\FixtureHandler\FixtureHandler;
use JobBoy\Clock\Domain\Clock;
use JobBoy\Clock\Domain\Infrastructure\Carbon\CarbonTimeFactory;
use JobBoy\Process\Application\Service\Work;
use JobBoy\Process\Application\Service\Work\Events\IdleTimeStarted;
use JobBoy\Process\Application\Service\Work\Events\MemoryLimitExceeded;
use JobBoy\Process\Application\Service\Work\Events\Timedout;
use JobBoy\Process\Application\Service\Work\Events\WorkLocked;
use JobBoy\Process\Application\Service\Work\Events\WorkReleased;
use JobBoy\Process\Domain\Entity\Data\ProcessData;
use JobBoy\Process\Domain\Entity\Factory\ProcessFactory;
use JobBoy\Process\Domain\IterationMaker\IterationMaker;
use JobBoy\Process\Domain\Lock\Infrastructure\InMemory\LockFactory;
use JobBoy\Process\Domain\MemoryControl\MemoryControl;
use JobBoy\Process\Domain\MemoryControl\NullMemoryControl;
use JobBoy\Process\Domain\PauseControl\NullPauseControl;
use JobBoy\Process\Domain\ProcessHandler\IterationResponse;
use JobBoy\Process\Domain\ProcessStatus;
use JobBoy\Process\Domain\Repository\Infrastructure\InMemory\ProcessRepository;
use PHPUnit\Framework\TestCase;
use Tests\JobBoy\Process\Test\Domain\Event\SpyEventBus;

class WorkTest extends TestCase
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
            ->andReturnUsing(function () use ($processRepository) {
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

        $memoryLimit = new NullMemoryControl();
        $pauseControl = new NullPauseControl();
        $eventBus = new SpyEventBus();

        $lockFactory = new LockFactory();

        $service = new Work(
            $iterationMaker,
            $lockFactory,
            $eventBus,
            $memoryLimit,
            $pauseControl
        );

        $this->assertProcessRepositoryEquals([
            'job1' => 'starting',
            'job2' => 'starting',
        ], $processRepository);


        $this->aFewMinutesLater($fh);
        $service->execute(0, 0);

        $this->assertProcessRepositoryEquals([
            'job2' => 'starting',
            'job1' => 'running',
        ], $processRepository);

        $this->aFewMinutesLater($fh);
        $service->execute(0, 0);

        $this->assertProcessRepositoryEquals([
            'job2' => 'starting',
            'job1' => 'completed',
        ], $processRepository);

        $this->aFewMinutesLater($fh);
        $service->execute(0, 0);

        $this->assertProcessRepositoryEquals([
            'job1' => 'completed',
            'job2' => 'running',
        ], $processRepository);

        $this->aFewMinutesLater($fh);
        $service->execute(0, 0);

        $this->assertProcessRepositoryEquals([
            'job1' => 'completed',
            'job2' => 'completed',
        ], $processRepository);

        $this->aFewMinutesLater($fh);
        $service->execute(0, 0);

        $this->assertProcessRepositoryEquals([
            'job1' => 'completed',
            'job2' => 'completed',
        ], $processRepository);


        $this->assertEventBusEquals([
            ['class' => WorkLocked::class, 'text' => 'Work service locked', 'parameters' => []],
            ['class' => Timedout::class, 'text' => 'Timeout: {{seconds}} seconds', 'parameters' => ['seconds' => 0]],
            ['class' => WorkReleased::class, 'text' => 'Work service released', 'parameters' => []],

            ['class' => WorkLocked::class, 'text' => 'Work service locked', 'parameters' => []],
            ['class' => Timedout::class, 'text' => 'Timeout: {{seconds}} seconds', 'parameters' => ['seconds' => 0]],
            ['class' => WorkReleased::class, 'text' => 'Work service released', 'parameters' => []],

            ['class' => WorkLocked::class, 'text' => 'Work service locked', 'parameters' => []],
            ['class' => Timedout::class, 'text' => 'Timeout: {{seconds}} seconds', 'parameters' => ['seconds' => 0]],
            ['class' => WorkReleased::class, 'text' => 'Work service released', 'parameters' => []],

            ['class' => WorkLocked::class, 'text' => 'Work service locked', 'parameters' => []],
            ['class' => Timedout::class, 'text' => 'Timeout: {{seconds}} seconds', 'parameters' => ['seconds' => 0]],
            ['class' => WorkReleased::class, 'text' => 'Work service released', 'parameters' => []],

            ['class' => WorkLocked::class, 'text' => 'Work service locked', 'parameters' => []],
            ['class' => IdleTimeStarted::class, 'text' => 'Idle time for {{seconds}} seconds', 'parameters' => ['seconds' => 0]],
            ['class' => Timedout::class, 'text' => 'Timeout: {{seconds}} seconds', 'parameters' => ['seconds' => 0]],
            ['class' => WorkReleased::class, 'text' => 'Work service released', 'parameters' => []],
        ], $eventBus);

    }


    /**
     * @test
     */
    public function it_checks_memory()
    {

        $fh = $this->createFixtureHandler();

        $processRepository = new ProcessRepository();
        $processFactory = new ProcessFactory();

        $this->aFewMinutesAgo($fh);

        $process = $processFactory->create(new ProcessData([
            'code' => 'job'
        ]));
        $processRepository->add($process);

        $iterationMaker = \Mockery::mock(IterationMaker::class);
        $iterationMaker->shouldReceive('work')
            ->andReturnUsing(function () use ($process) {
                $process->changeStatusToCompleted();
                return new IterationResponse(true);
            });

        $memoryLimit = new class() implements MemoryControl {

            public function limit(): int
            {
                return 100;
            }

            public function isLimitExceeded(): bool
            {
                return true;
            }

            public function usage(): int
            {
                return 110;
            }
        };

        $pauseControl = new NullPauseControl();
        $eventBus = new SpyEventBus();

        $lockFactory = new LockFactory();

        $service = new Work(
            $iterationMaker,
            $lockFactory,
            $eventBus,
            $memoryLimit,
            $pauseControl
        );

        $this->assertProcessRepositoryEquals([
            'job' => 'starting',
        ], $processRepository);


        $this->aFewMinutesLater($fh);
        $service->execute(0, 0);

        $this->assertProcessRepositoryEquals([
            'job' => 'completed',
        ], $processRepository);

        $this->assertEventBusEquals([
            ['class' => WorkLocked::class, 'text' => 'Work service locked', 'parameters' => []],
            ['class' => MemoryLimitExceeded::class, 'text' => 'Memory limit exceeded', 'parameters' => ['usage' => '110B']],
            ['class' => WorkReleased::class, 'text' => 'Work service released', 'parameters' => []],

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
            TestCase::assertEquals($expected[$i], $event->toArray(), 'index: ' . $i);
        }
    }


}
