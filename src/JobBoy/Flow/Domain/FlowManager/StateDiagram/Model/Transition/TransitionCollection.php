<?php
declare(strict_types=1);

namespace JobBoy\Flow\Domain\FlowManager\StateDiagram\Model\Transition;

use Assert\Assertion;
use JobBoy\Flow\Domain\FlowManager\StateDiagram\Model\State\StateCode;

class TransitionCollection
{
    /** @var array<string,Transition> */
    private $transitions = [];

    private function __construct()
    {
    }

    public static function create(): self
    {
        return new self();
    }

    public function set(Transition $transition): self
    {
        $this->assertTransitionIsNotSetYet($transition);

        $clone = clone $this;
        $clone->transitions[(string)$transition] = $transition;
        return $clone;
    }

    public function has(Transition $transition): bool
    {
        return isset($this->transitions[(string)$transition]);
    }

    public function all(): array
    {
        return array_values($this->transitions);
    }

    /** @return Transition[] */
    public function byStateCode(StateCode $stateCode): array
    {
        return array_filter($this->all(), function (Transition $transition) use ($stateCode) {
            if ((string)$transition->to() === (string)$stateCode) {
                return true;
            }
            if ((string)$transition->from() === (string)$stateCode) {
                return true;
            }
            return false;
        });
    }


    public function assertTransitionIsNotSetYet(Transition $transition): void
    {
        Assertion::keyNotExists($this->transitions, (string)$transition, sprintf('Transition "%s" is already set', $transition));
    }

}
