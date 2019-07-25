<?php

namespace JobBoy\Step\Domain\StepManager;

use Assert\Assertion;

class StepRegistry
{
    const DEFAULT_POSITION = 0;

    private $frozen = false;
    private $registeredSteps = [];
    private $unsortedSteps = [];
    private $steps = [];

    public function add(StepData $data, ?int $position = null): void
    {
        if ($this->frozen) {
            throw new \LogicException('The registry is frozen. You cannot add anything.');
        }

        if (!$position) {
            $position = self::DEFAULT_POSITION;
        }

        Assertion::notInArray((string)$data, $this->registeredSteps, sprintf('The step "%s" is registered yet', (string)$data));
        $this->registeredSteps[] = (string)$data;


        if (!isset($this->unsortedSteps[$data->job()])) {
            $this->unsortedSteps[$data->job()] = [];
        }

        if (!isset($this->steps[$data->job()][$position])) {
            $this->unsortedSteps[$data->job()][$position] = [];
        }

        $this->unsortedSteps[$data->job()][$position][] = $data->step();
    }

    public function get(string $job): array
    {
        $this->ensureAreSorted();

        Assertion::keyExists($this->steps, $job);
        return $this->steps[$job];
    }

    private function ensureAreSorted(): void
    {
        if ($this->frozen) {
            return;
        }

        foreach ($this->unsortedSteps as $job => $stepsByJob) {
            ksort($stepsByJob);
            foreach ($stepsByJob as $position => $stepsByPos) {
                foreach ($stepsByPos as $step) {
                    $this->steps[$job][] = $step;
                }
            }
            Assertion::minCount($this->steps[$job],1);
        }

        $this->frozen = true;
    }

}