<?php
declare(strict_types=1);

namespace JobBoy\Flow\Domain\FlowManager\StateDiagram\Builder;

use JobBoy\Flow\Domain\FlowManager\StateDiagram\Event\Event;
use JobBoy\Flow\Domain\FlowManager\StateDiagram\Event\EventCode;
use JobBoy\Flow\Domain\FlowManager\StateDiagram\Model\State\State;
use JobBoy\Flow\Domain\FlowManager\StateDiagram\Model\State\StateCode;
use JobBoy\Flow\Domain\FlowManager\StateDiagram\Model\State\StateCollection;
use JobBoy\Flow\Domain\FlowManager\StateDiagram\Model\Transition\EntryStateCollection;
use JobBoy\Flow\Domain\FlowManager\StateDiagram\Model\Transition\Transition;
use JobBoy\Flow\Domain\FlowManager\StateDiagram\Model\Transition\TransitionCollection;

class StateBuilder implements ParentStateBuilder
{
    const ACTIVE = 'active';

    /** @var ParentStateBuilder */
    private $parentStateBuilder;

    /** @var State */
    private $state;

    /** @var StateCollection */
    private $states;

    /** @var TransitionCollection */
    private $transitions;

    /** @var EntryStateCollection */
    private $entryStates;


    private function __construct(
        ParentStateBuilder $parentStateBuilder,
        string $code,
        string $name
    )
    {

        $state = State::fromString($code, $name);
        if ($parentStateBuilder->_code()) {
            $state = $state->setParent($parentStateBuilder->_code());
        }

        $this->parentStateBuilder = $parentStateBuilder;
        $this->state = $state;
        $this->states = StateCollection::createPartial()->set($state);
        $this->transitions = TransitionCollection::create();
        $this->entryStates = EntryStateCollection::create();

    }

    public static function create(
        ParentStateBuilder $parentStateBuilder,
        string $code,
        ?string $name = null
    ): self
    {
        return new self(
            $parentStateBuilder,
            $code,
            $name ?? $code
        );
    }

    public function createChild(string $code, ?string $name = null): self
    {
        $child = State::fromString($code, $name)
            ->setParent($this->state->code());

        $clone = clone $this;

        $clone->states = $clone->states->set($child, self::ACTIVE);

        return $clone;
    }

    public function asEntry(): self
    {
        $clone = clone $this;

        $clone->states->assertTagIsSet(self::ACTIVE);
        $activeState = $clone->states->getTagged(self::ACTIVE);

        $clone->entryStates = $clone->entryStates->set($activeState->parent(), $activeState->code());

        $transition = Transition::entry($activeState->code());
        $clone->transitions = $clone->transitions->set($transition);

        return $clone;
    }

    public function asExit(string $on, ?string $name=null): self
    {
        $clone = clone $this;

        $activeState = $clone->states->getTagged(self::ACTIVE);

        $transition = Transition::exit(
            $activeState->code(),
            Event::create(new EventCode($on), $name??$on)
        );

        $clone->transitions = $clone->transitions->set($transition);

        return $clone;
    }

    public function createChildBuilder(string $code, ?string $name = null): self
    {
        return self::create(
            $this,
            $code,
            $name ?? $code
        );
    }


    public function build(): ParentStateBuilder
    {

        $clone = clone $this->parentStateBuilder;

        foreach ($this->states->all() as $state) {
            $clone = $clone->_setState($state);
        }

        $clone = $clone->_tagState($this->state->code(), self::ACTIVE);

        foreach ($this->transitions->all() as $transition) {
            $clone = $clone->_setTransition($transition);
        }

        return $clone;
    }

    public function _code(): ?StateCode
    {
        return $this->state->code();
    }

    public function _setState(State $state): ParentStateBuilder
    {

        $clone = clone $this;
        $clone->states = $clone->states->set($state);

        return $clone;
    }

    public function _tagState(StateCode $stateCode, string $tag): ParentStateBuilder
    {
        $clone = clone $this;
        $clone->states = $clone->states->tag($stateCode, $tag);

        return $clone;
    }

    public function _setTransition(Transition $transition): ParentStateBuilder
    {
        $clone = clone $this;
        $clone->transitions = $clone->transitions->set($transition);

        return $clone;
    }

}
