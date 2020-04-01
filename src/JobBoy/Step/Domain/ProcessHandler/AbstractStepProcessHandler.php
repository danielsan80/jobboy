<?php

namespace JobBoy\Step\Domain\ProcessHandler;

use JobBoy\Process\Domain\Entity\Id\ProcessId;
use JobBoy\Process\Domain\Entity\Process;
use JobBoy\Process\Domain\ProcessHandler\ProcessHandlerInterface;
use JobBoy\Process\Domain\Repository\ProcessRepositoryInterface;
use JobBoy\Step\Domain\StepManager\HasStepDataInterface;
use JobBoy\Step\Domain\StepManager\StepData;
use JobBoy\Step\Domain\StepManager\StepManager;

/**
 * @deprecated static abstract methods are not a good idea
 * @see AbstractUnhandledProcessHandler2
 */
abstract class AbstractStepProcessHandler implements ProcessHandlerInterface, HasStepDataInterface
{

    /** @var ProcessRepositoryInterface */
    protected $processRepository;
    /** @var StepManager */
    protected $stepManager;

    abstract static protected function job(): string;

    abstract static protected function step(): string;

    public function __construct(
        ProcessRepositoryInterface $processRepository,
        StepManager $stepManager
    )
    {
        $this->processRepository = $processRepository;
        $this->stepManager = $stepManager;
    }

    protected function process(ProcessId $id): ?Process
    {
        return $this->processRepository->byId($id);
    }

    protected function nextStep(ProcessId $id): void
    {
        $this->stepManager->nextStep($id);
    }

    public function supports(ProcessId $id): bool
    {
        return !$this->process($id)->isHandled()
            && $this->process($id)->status()->isRunning()
            && $this->process($id)->code() === static::job()
            && $this->stepManager->atStep($id, static::step());
    }


    public function stepData(): StepData
    {
        return new StepData(static::job(), static::step());
    }

}