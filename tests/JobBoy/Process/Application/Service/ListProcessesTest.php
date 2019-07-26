<?php

namespace Tests\JobBoy\Process\Application\Service;

use JobBoy\Process\Application\DTO\Process;
use JobBoy\Process\Domain\Entity\Data\ProcessData;
use JobBoy\Process\Domain\Entity\Factory\ProcessFactory;
use JobBoy\Process\Domain\Entity\Id\ProcessId;
use JobBoy\Process\Domain\Repository\Infrastructure\InMemory\ProcessRepository;
use PHPUnit\Framework\TestCase;

use JobBoy\Process\Application\Service\ListProcesses;
use Ramsey\Uuid\Uuid;

class ListProcessesTest extends TestCase
{

    /**
     * @test
     */
    public function it_works()
    {

        $processRepository = new ProcessRepository();
        $processFactory = new ProcessFactory();

        $id = Uuid::uuid4();

        $processRepository->add($processFactory->create(new ProcessData([
            'id' => new ProcessId($id),
            'code' => 'job1'
        ])));

        $service = new ListProcesses($processRepository);

        $processes = $service->execute();

        $this->assertCount(1, $processes);

        $process = $processes[0];

        $this->assertInstanceOf(Process::class, $process);
        $this->assertEquals((string)$id, $process->id());
        $this->assertEquals('job1', $process->code());
        $this->assertEquals('starting', $process->status());
        $this->assertFalse($process->isHandled());

    }

}
