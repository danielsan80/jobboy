<?php

namespace Tests\JobBoy\Process\Application\Service;

use Dan\FixtureHandler\FixtureHandler;
use JobBoy\Clock\Domain\Clock;
use JobBoy\Clock\Domain\Infrastructure\Carbon\CarbonTimeFactory;
use JobBoy\Process\Domain\Entity\Data\ProcessData;
use JobBoy\Process\Domain\Entity\Factory\ProcessFactory;
use JobBoy\Process\Domain\ProcessParameters;
use JobBoy\Process\Domain\Repository\Infrastructure\InMemory\ProcessRepository;
use PHPUnit\Framework\TestCase;

use JobBoy\Process\Application\Service\RemoveOldProcesses;

class RemoveOldProcessesTest extends TestCase
{

    /**
     * @test
     */
    public function it_works()
    {
        $fh = $this->createFixtureHandler();


        $processRepository = new ProcessRepository();
        $processFactory = new ProcessFactory();


        $this->aFewDaysAgo($fh, 100);

        $process = $processFactory->create(new ProcessData([
            'code' => 'stale_process_job',
            'parameters' => new ProcessParameters(['a_key' => 'a_value']),

        ]));
        $processRepository->add($process);


        $this->aFewDaysAgo($fh, 80);

        $process = $processFactory->create(new ProcessData([
            'code' => 'staling_process_job',
            'parameters' => new ProcessParameters(['a_key' => 'a_value']),

        ]));
        $processRepository->add($process);


        $this->aFewDaysAgo($fh, 1);

        $process = $processFactory->create(new ProcessData([
            'code' => 'fresh_process_job',
            'parameters' => new ProcessParameters(['a_key' => 'a_value']),

        ]));
        $processRepository->add($process);


        $this->atNow($fh);

        TestCase::assertCount(3, $processRepository->all());

        $removeOldProcesses = new RemoveOldProcesses($processRepository);

        $removeOldProcesses->execute(90);

        TestCase::assertCount(2, $processRepository->all());
        TestCase::assertEquals('staling_process_job', $processRepository->all()[0]->code());
        TestCase::assertEquals('fresh_process_job', $processRepository->all()[1]->code());

        $this->inAFewDays($fh, 20);

        $removeOldProcesses->execute(90);

        TestCase::assertCount(1, $processRepository->all());
        TestCase::assertEquals('fresh_process_job', $processRepository->all()[0]->code());

    }

    protected function createFixtureHandler():FixtureHandler
    {
        $fh = new FixtureHandler();

        $timeFactory = new CarbonTimeFactory();
        Clock::setTimeFactory($timeFactory);

        $fh->setRef('time', $timeFactory);

        return $fh;
    }

    public function atNow(FixtureHandler $fh)
    {
        $fh->getRef('time')->unfreeze();
    }


    public function aFewDaysAgo(FixtureHandler $fh, $daysAgo = 100)
    {
        $fh->getRef('time')->freeze(sprintf('-%d days', $daysAgo));
    }

    public function inAFewDays(FixtureHandler $fh, $aFewDays = 10)
    {
        $fh->getRef('time')->freeze(sprintf('+%d days', $aFewDays));
    }


}
