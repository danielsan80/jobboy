<?php
declare(strict_types=1);

namespace JobBoy\Flow\Domain\FlowManager\StateDiagram\Model\State;

use Assert\Assertion;

class StateCollection
{
    const COMPLETE = 'complete';
    const PARTIAL = 'partial';

    private $type;

    /** @var array<string,State> */
    private $states = [];

    /** @var array<string,StateCode> */
    private $tags = [];

    private function __construct(string $type = self::COMPLETE)
    {
        $this->type = $type;
    }

    public static function create(): self
    {
        return new self(self::COMPLETE);
    }

    public static function createPartial(): self
    {
        return new self(self::PARTIAL);
    }

    public function set(State $state, ?string $tag=null): self
    {
        $this->assertStateIsNotSetYet($state);
        $this->assertParentStateIsSetYet($state);

        $clone = clone $this;
        $clone->states[(string)$state] = $state;
        if ($tag) {
            $clone->tags[$tag] = $state->code();
        }
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

    public function tag(StateCode $code, string $tag): self
    {
        $this->assertStateIsSet($code);

        $clone = clone $this;
        $clone->tags[$tag] = $code;
        return $clone;
    }

    public function untag(string $tag): self
    {
        $clone = clone $this;
        unset($clone->tags[$tag]);
        return $clone;
    }

    public function getTagged(string $tag): ?State
    {
        if (!isset($this->tags[$tag])) {
            return null;
        }

        return $this->get($this->tags[$tag]);
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

    public function assertTagIsSet(string $tag): void
    {
        Assertion::keyExists($this->tags, $tag, sprintf('Tag "%s" is not set yet', $tag));
    }


    private function assertStateIsNotSetYet(State $state): void
    {
        Assertion::keyNotExists($this->states, (string)$state, sprintf('State "%s" is already set', $state));
    }

    private function assertParentStateIsSetYet(State $state): void
    {
        if ($this->type === self::PARTIAL) {
            return;
        }

        if (!$state->parent()) {
            return;
        }

        Assertion::true($this->has($state->parent()), sprintf('Parent state "%s" is not set yet', (string)$state->parent()));
    }
}
