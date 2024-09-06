<?php
declare(strict_types=1);

namespace JobBoy\Flow\Domain\FlowManager\StateDiagram\Model\State;

use Assert\Assertion;

class StateStack
{
    /** @var State[]|false */
    private $stack = [false];

    private function __construct()
    {
    }

    public static function create(): self
    {
        return new self();
    }

    public function replace(State $state): self
    {
        $clone = clone $this;

        $stack = $clone->stack;

        array_pop($stack);

        $stack[] = $state;

        $clone->stack = $stack;
        return $clone;
    }

    public function open(): self
    {
        $clone = clone $this;

        $stack = $clone->stack;

        Assertion::true($stack[count($stack) - 1] instanceof State, 'No state to open');

        $stack[] = false;

        $clone->stack = $stack;
        return $clone;
    }

    public function close(): self
    {
        $clone = clone $this;

        $stack = $clone->stack;

        Assertion::isInstanceOf($stack[count($stack) - 1], State::class, 'What to close?');

        array_pop($stack);

        $clone->stack = $stack;
        return $clone;
    }

    public function current(): State
    {
        $stack = $this->stack;

        $state = $stack[count($stack) - 1];

        Assertion::isInstanceOf($state, State::class, 'No current state');

        return $state;
    }

    public function parent(): ?State
    {
        $stack = $this->stack;

        if (count($stack) === 1) {
            return null;
        }

        return $stack[count($stack) - 2];
    }

}
