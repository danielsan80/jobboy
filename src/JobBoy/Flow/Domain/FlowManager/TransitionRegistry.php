<?php

namespace JobBoy\Flow\Domain\FlowManager;

use Assert\Assertion;

class TransitionRegistry
{
    private $frozen = false;
    private $registeredTransitions = [];
    private $transitions = [];
    private $entries = [];

    public function add(Transition $transition): void
    {
        if ($this->frozen) {
            throw new \LogicException('The registry is frozen. You cannot add anything.');
        }

        Assertion::notInArray((string)$transition, $this->registeredTransitions, sprintf('The transition "%s" is registered yet', (string)$transition));
        $this->registeredTransitions[] = (string)$transition;

        if ($transition->isEntry()) {
            if (isset($this->entries[$transition->job()])) {
                throw new \InvalidArgumentException(sprintf(
                    'The transition "%s" is an entry but an entry is set yet for job "%s": "%s"',
                    (string)$transition,
                    $transition->job(),
                    (string)$this->entries[$transition->job()]
                ));
            }
            $this->entries[$transition->job()] = $transition;
            return;
        }

        if (isset(
            $this->transitions[$transition->job()][$transition->from()->code()][$transition->on()]
        )) {
            throw new \LogicException(sprintf(
                'A transition for "%s" is set yet',
                (string)$transition)
            );
        }

        $this->transitions[$transition->job()][$transition->from()->code()][$transition->on()] = $transition;
    }

    public function getEntry(string $job): Transition
    {
        $this->freeze();
        $this->assertJobAsAnEntry($job);
        return $this->entries[$job];
    }

    public function get(Node $from, string $on): Transition
    {
        $this->freeze();
        $this->assertJobAsAnEntry($from->job());
        if (!isset(
            $this->transitions[$from->job()][$from->code()][$on]
        )) {
            throw new \LogicException(sprintf('Is not set a transition for the "%s" on "%s"', (string)$from, $on));
        }
        return $this->transitions[$from->job()][$from->code()][$on];
    }

    private function freeze(): void
    {
        $this->frozen = true;
    }

    private function assertJobAsAnEntry(string $job): void
    {
        if (!isset( $this->entries[$job])) {
            throw new \LogicException(sprintf('Is not set an entry transition for the job "%s"', $job));
        }
    }

}