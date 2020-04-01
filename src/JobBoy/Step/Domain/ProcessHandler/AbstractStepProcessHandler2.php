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
 * This new version of the AbstractStepProcessHandler allows to inject job and step instead to use inheritance.
 * Use this one
 */
abstract class AbstractStepProcessHandler2 implements ProcessHandlerInterface, HasStepDataInterface
{

    /** @var ProcessRepositoryInterface */
    protected $processRepository;
    /** @var StepManager */
    protected $stepManager;

    abstract protected function job(): string;

    abstract protected function step(): string;

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
            && $this->process($id)->code() === $this->job()
            && $this->stepManager->atStep($id, $this->step());
    }


    public function stepData(): StepData
    {
        return new StepData($this->job(), $this->step());
    }

}