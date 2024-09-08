<?php
declare(strict_types=1);

namespace JobBoy\Flow\Domain\FlowManager\JobSchema\Model\State;

use Assert\Assertion;

class StateCollection
{
    /** @var array<string,State> */
    private $states = [];


    private function __construct()
    {
    }

    public static function create(): self
    {
        return new self();
    }

    public function set(State $state): self
    {
        $this->assertStateIsNotSetYet($state);
        $this->assertParentStateIsSetYet($state);

        $clone = clone $this;
        $clone->states[(string)$state] = $state;
        return $clone;
    }

    public function has(StateCode $code): bool
    {
        return isset($this->states[(string)$code]);
    }

    public function get(StateCode $code): ?State
    {
        if (!$this->has($code)) {
            return null;
        }

        return $this->states[(string)$code];
    }

    public function all(): array
    {
        return array_values($this->states);
    }

    public function getParent(StateCode $code): ?State
    {
        $this->assertStateIsSet($code);

        $state = $this->get($code);

        if (!$state->parent()) {
            return null;
        }

        return $this->get($state->parent());
    }

    public function getChildren(?StateCode $code): array
    {
        if ($code) {
            $this->assertStateIsSet($code);
        }

        return array_filter($this->all(), function (State $state) use ($code) {
            if (!$code) {
                return !$state->parent();
            }
            return (string)$state->parent() === (string)$code;
        });
    }

    public function hasChildren(StateCode $code): bool
    {
        return count($this->getChildren($code)) > 0;
    }

    public function assertStateIsSet(StateCode $code): void
    {
        Assertion::keyExists($this->states, (string)$code, sprintf('State "%s" is not set yet', $code));
    }

    public function assertStatesHaveSameParent(StateCode $state1, StateCode $state2): void
    {
        $this->assertStateIsSet($state1);
        $this->assertStateIsSet($state2);

        $parent1 = $this->getParent($state1);
        $parent2 = $this->getParent($state2);

        Assertion::eq((string)$parent1, (string)$parent2, sprintf('States "%s" and "%s" have different parents', (string)$state1, (string)$state2));
    }

    private function assertStateIsNotSetYet(State $state): void
    {
        Assertion::keyNotExists($this->states, (string)$state, sprintf('State "%s" is already set', $state));
    }

    private function assertParentStateIsSetYet(State $state): void
    {
        if (!$state->parent()) {
            return;
        }

        Assertion::true($this->has($state->parent()), sprintf('Parent state "%s" is not set yet', (string)$state->parent()));
    }
}
