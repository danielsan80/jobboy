<?php

namespace JobBoy\Step\Domain\StepManager;

use Assert\Assertion;

class StepData
{
    /** @var string */
    protected $job;
    /** @var string */
    protected $step;

    public function __construct(string $job, string $step)
    {
        Assertion::notBlank($job);
        Assertion::notBlank($step);
        $this->job = $job;
        $this->step = $step;
    }

    public function job(): string
    {
        return $this->job;
    }

    public function step(): string
    {
        return $this->step;
    }

    public function __toString()
    {
        return $this->job.'.'.$this->step;
    }

}