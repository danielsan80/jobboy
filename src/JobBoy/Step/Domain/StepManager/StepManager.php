<?php

namespace JobBoy\Step\Domain\StepManager;

use JobBoy\Process\Domain\Entity\Id\ProcessId;
use JobBoy\Process\Domain\Entity\Process;
use JobBoy\Process\Domain\Repository\ProcessRepositoryInterface;

class StepManager
{
    const STEP = 'step';

    /** @var ProcessRepositoryInterface */
    protected $processRepository;
    /** @var StepRegistry */
    protected $stepRegistry;

    public function __construct(ProcessRepositoryInterface $processRepository, StepRegistry $stepRegistry)
    {
        $this->processRepository = $processRepository;
        $this->stepRegistry = $stepRegistry;
    }

    protected function process(ProcessId $id): ?Process
    {
        return $this->processRepository->byId($id);
    }

    public function resetStep(ProcessId $id): void
    {
        $process = $this->process($id);

        $steps = $this->stepRegistry->get($process->code());

        $firstStep = $steps[0];

        $this->process($id)->set(self::STEP, $firstStep);
    }

    public function atStep(ProcessId $id, string $step): bool
    {
        return $this->process($id)->get(self::STEP) === $step;
    }


    public function nextStep(ProcessId $id): ?string
    {
        $currentStep = $this->currentStep($id);
        $steps = $this->stepRegistry->get($this->job($id));
        $found = false;

        foreach ($steps as $step) {
            if ($found) {
                $this->process($id)->set(self::STEP, $step);
                return $step;
            }
            if ($step === $currentStep) {
                $found = true;
            }
        }
        $this->process($id)->unset(self::STEP);
        return null;
    }

    public function isWalking(ProcessId $id): bool
    {
        return (bool)$this->currentStep($id);
    }

    protected function currentStep(ProcessId $id): ?string
    {
        return $this->process($id)->get(self::STEP);
    }

    protected function job(ProcessId $id): string
    {
        return $this->process($id)->code();
    }

}
