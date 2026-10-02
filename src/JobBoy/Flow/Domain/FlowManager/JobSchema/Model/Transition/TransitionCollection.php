<?php
declare(strict_types=1);

namespace JobBoy\Flow\Domain\FlowManager\JobSchema\Model\Transition;

use Assert\Assertion;
use JobBoy\Flow\Domain\FlowManager\JobSchema\Model\State\StateCode;
use Traversable;

class TransitionCollection implements \Iterator
{
    /** @var \ArrayIterator */
    private $transitions;

    private function __construct()
    {
        $this->transitions = new \ArrayIterator();
    }

    public static function create(): self
    {
        return new self();
    }

    public function isEmpty(): bool
    {
        return $this->transitions->count() === 0;
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

    public function remove(Transition $transition): self
    {
        $clone = clone $this;
        unset($clone->transitions[(string)$transition]);
        return $clone;
    }

    public function union(self $transitionCollection): self
    {
        $clone = clone $this;

        foreach ($transitionCollection as $transition) {
            if ($clone->has($transition)) {
                continue;
            }
            $clone = $clone->set($transition);
        }

        return $clone;
    }

    public function difference(self $transitionCollection): self
    {
        $clone = clone $this;

        foreach ($transitionCollection as $transition) {
            $clone = $clone->remove($transition);
        }

        return $clone;
    }

    public function all(): array
    {
        return array_values(iterator_to_array($this->transitions));
    }

    public function relatingToState(?StateCode $stateCode): TransitionCollection
    {
        if (!$stateCode) {
            return self::create();
        }

        $transitions = [];

        foreach ($this->transitions as $key => $transition) {

            if ((string)$transition->to() === (string)$stateCode) {
                $transitions[$key] = $transition;
                continue;
            }

            if ((string)$transition->from() === (string)$stateCode) {
                $transitions[$key] = $transition;
                continue;
            }
        }

        $transitionCollection = new self();
        $transitionCollection->transitions = $transitions;
        return $transitionCollection;
    }

    public function changesFromState(?StateCode $stateCode): TransitionCollection
    {

        if (!$stateCode) {
            return self::create();
        }

        $transitions = [];

        foreach ($this->transitions as $key => $transition) {
            if (!$transition->type()->isChange()) {
                continue;
            }

            if ((string)$transition->from() !== (string)$stateCode) {
                continue;
            }

            $transitions[$key] = $transition;
        }

        $transitionCollection = new self();
        $transitionCollection->transitions = $transitions;
        return $transitionCollection;

    }

    public function changesToState(?StateCode $stateCode): TransitionCollection
    {

        if (!$stateCode) {
            return self::create();
        }

        if (!$stateCode) {
            return $this;
        }

        $transitions = [];

        foreach ($this->transitions as $key => $transition) {
            if (!$transition->type()->isChange()) {
                continue;
            }

            if ((string)$transition->to() !== (string)$stateCode) {
                continue;
            }

            $transitions[$key] = $transition;
        }

        $transitionCollection = new self();
        $transitionCollection->transitions = $transitions;
        return $transitionCollection;

    }

    public function entriesToState(?StateCode $stateCode): TransitionCollection
    {
        if (!$stateCode) {
            return self::create();
        }

        $transitions = [];

        foreach ($this->transitions as $key => $transition) {
            if (!$transition->type()->isEntry()) {
                continue;
            }

            if ((string)$transition->to() !== (string)$stateCode) {
                continue;
            }

            $transitions[$key] = $transition;
        }

        $transitionCollection = new self();
        $transitionCollection->transitions = $transitions;
        return $transitionCollection;

    }

    public function exitsFromState(?StateCode $stateCode): TransitionCollection
    {
        if (!$stateCode) {
            return self::create();
        }

        $transitions = [];

        foreach ($this->transitions as $key => $transition) {
            if (!$transition->type()->isExit()) {
                continue;
            }

            if ((string)$transition->from() !== (string)$stateCode) {
                continue;
            }

            $transitions[$key] = $transition;
        }

        $transitionCollection = new self();
        $transitionCollection->transitions = $transitions;
        return $transitionCollection;

    }

    public function filter(callable $filter): self
    {
        $transitions = [];

        foreach ($this->transitions as $key => $transition) {
            if ($filter($transition)) {
                $transitions[$key] = $transition;
            }
        }

        $transitionCollection = new self();
        $transitionCollection->transitions = $transitions;
        return $transitionCollection;
    }




    public function assertTransitionIsNotSetYet(Transition $transition): void
    {
        Assertion::keyNotExists($this->transitions, (string)$transition, sprintf('Transition "%s" is already set', $transition));
    }

    /**
     * @return Transition
     */
    public function current()
    {
        return $this->transitions->current();
    }

    public function next()
    {
        $this->transitions->next();
    }

    /**
     * @return string
     */
    public function key()
    {
        return $this->transitions->key();
    }

    public function valid()
    {
        return $this->transitions->valid();
    }

    public function rewind()
    {
        $this->transitions->rewind();
    }
}
