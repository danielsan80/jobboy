<?php

namespace Tests\JobBoy\Process\Application\Service;

use JobBoy\Process\Domain\Entity\Factory\ProcessFactory;
use JobBoy\Process\Domain\Repository\Infrastructure\InMemory\ProcessRepository;
use PHPUnit\Framework\TestCase;

use JobBoy\Process\Application\Service\StartProcess;

class StartProcessTest extends TestCase
{

    /**
     * @test
     */
    public function it_works()
    {

        $processRepository = new ProcessRepository();

        $service = new StartProcess(
            new ProcessFactory(),
            $processRepository
        );

        $this->assertCount(0, $processRepository->all());

        $service->execute('a_job', ['a_key' => 'a_value']);

        $this->assertCount(1, $processRepository->all());

        $this->assertEquals('starting', (string)$processRepository->all()[0]->status());


    }

}
