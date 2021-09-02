<?php
declare(strict_types=1);

namespace JobBoy\Flow\Domain\FlowManager\TransitionLoader;

use Assert\Assertion;

class TransitionSet
{
    /** @var string */
    private $job;
    /** @var Transition[] */
    private $transitions;

    public function __construct(string $job, array $transitions)
    {
        Assertion::notBlank($job);
        Assertion::allIsInstanceOf($transitions, Transition::class);
        $this->job = $job;
        $this->transitions = $transitions;
    }

    public function job(): string
    {
        return $this->job;
    }

    /** @return Transition[] */
    public function transitions(): array
    {
        return $this->transitions;
    }
}
