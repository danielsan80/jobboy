<?php
declare(strict_types=1);

namespace JobBoy\Flow\Domain\FlowManager\StateDiagram\Model\State;

use Assert\Assertion;

class StateCollection
{
    /** @var array<string,State> */
    private $states = [];

    public function set(State $state): self
    {
        $this->assertStateIsNotSetYet($state);
        $clone = clone $this;
        $clone->states[(string)$state] = $state;
        return $clone;
    }

    private function assertStateIsNotSetYet(State $state): void
    {
        Assertion::notKeyExists($this->states, (string)$state, sprintf('State %s already set', $state));
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
}
