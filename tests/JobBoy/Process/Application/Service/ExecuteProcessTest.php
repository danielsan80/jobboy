<?php

namespace Tests\JobBoy\Process\Application\Service;

use JobBoy\Process\Domain\Entity\Factory\ProcessFactory;
use JobBoy\Process\Domain\IterationMaker\IterationMaker;
use JobBoy\Process\Domain\ProcessIterator\IterationResponse;
use JobBoy\Process\Domain\Repository\Infrastructure\InMemory\ProcessRepository;
use PHPUnit\Framework\TestCase;

use JobBoy\Process\Application\Service\ExecuteProcess;

class ExecuteProcessTest extends TestCase
{

    /**
     * @test
     */
    public function it_works()
    {

        $processRepository = new ProcessRepository();

        $iterationMaker = \Mockery::mock(IterationMaker::class);
        $iterationMaker->shouldReceive('work')
            ->twice()
            ->andReturnUsing(function() use ($processRepository) {
                $processes = $processRepository->all();
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

        $service = new ExecuteProcess(
            new ProcessFactory(),
            $processRepository,
            $iterationMaker
        );

        $service->execute('a_job', ['a_key' => 'a_value']);

        $this->assertCount(1, $processRepository->all());
        $this->assertEquals('completed', (string)$processRepository->all()[0]->status());


    }

}
