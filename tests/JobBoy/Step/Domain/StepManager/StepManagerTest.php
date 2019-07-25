<?php

namespace Tests\JobBoy\Step\Domain\StepManager;

use JobBoy\Step\Domain\StepManager\StepData;
use JobBoy\Step\Domain\StepManager\StepRegistry;
use JobBoy\Process\Domain\Entity\Data\ProcessData;
use JobBoy\Process\Domain\Entity\Factory\ProcessFactory;
use JobBoy\Process\Domain\Entity\Process;
use JobBoy\Process\Domain\Repository\Infrastructure\InMemory\ProcessRepository;
use PHPUnit\Framework\TestCase;

use JobBoy\Step\Domain\StepManager\StepManager;

class StepManagerTest extends TestCase
{

    /**
     * @test
     */
    public function it_works()
    {
        $processRepository = new ProcessRepository();
        $processFactory = new ProcessFactory(Process::class);

        $stepRegistry = new StepRegistry();
        $stepManager = new StepManager($processRepository, $stepRegistry);

        $stepRegistry->add(new StepData('cake','prepare'), 10);
        $stepRegistry->add(new StepData('cake','cook'), 20);
        $stepRegistry->add(new StepData('cake','eat'), 30);

        $stepRegistry->add(new StepData('bike','get_on'), 10);
        $stepRegistry->add(new StepData('bike','ride'), 20);
        $stepRegistry->add(new StepData('bike','brake'), 30);


        $process = $processFactory->create(new ProcessData([
            'code' => 'bike'
        ]));
        $processRepository->add($process);

        TestCase::assertNull($process->get('step'));
        TestCase::assertFalse($stepManager->isWalking($process->id()));
        TestCase::assertFalse($stepManager->atStep($process->id(), 'get_on'));

        $stepManager->resetStep($process->id());

        TestCase::assertEquals('get_on', $process->get('step'));
        TestCase::assertTrue($stepManager->isWalking($process->id()));
        TestCase::assertTrue($stepManager->atStep($process->id(), 'get_on'));

        $stepManager->nextStep($process->id());

        TestCase::assertEquals('ride', $process->get('step'));
        TestCase::assertTrue($stepManager->isWalking($process->id()));
        TestCase::assertTrue($stepManager->atStep($process->id(), 'ride'));

        $stepManager->nextStep($process->id());

        TestCase::assertEquals('brake', $process->get('step'));
        TestCase::assertTrue($stepManager->isWalking($process->id()));
        TestCase::assertTrue($stepManager->atStep($process->id(), 'brake'));

        $stepManager->nextStep($process->id());

        TestCase::assertNull($process->get('step'));
        TestCase::assertFalse($stepManager->isWalking($process->id()));

    }

}
