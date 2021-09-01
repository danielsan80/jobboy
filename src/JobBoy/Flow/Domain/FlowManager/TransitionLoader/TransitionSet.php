<?php
declare(strict_types=1);

namespace JobBoy\Flow\Domain\FlowManager\TransitionLoader;

use Assert\Assertion;

class TransitionSet
{
    /** @var Job */
    private $job;
    /** @var Transition[] */
    private $transitions;

    public function __construct(Job $job, array $transitions)
    {
        Assertion::allIsInstanceOf($transitions, Transition::class);
        $this->job = $job;
        $this->transitions = $transitions;
    }

    public function job(): Job
    {
        return $this->job;
    }

    /** @return Transition[] */
    public function transitions(): array
    {
        return $this->transitions;
    }
}
