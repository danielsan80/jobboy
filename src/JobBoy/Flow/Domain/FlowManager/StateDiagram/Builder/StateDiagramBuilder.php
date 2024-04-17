<?php
declare(strict_types=1);

namespace JobBoy\Flow\Domain\FlowManager\StateDiagram\Builder;

use Assert\Assertion;
use JobBoy\Flow\Domain\FlowManager\StateDiagram\Event\Event;
use JobBoy\Flow\Domain\FlowManager\StateDiagram\Event\EventCode;
use JobBoy\Flow\Domain\FlowManager\StateDiagram\Model\Job\Job;
use JobBoy\Flow\Domain\FlowManager\StateDiagram\Model\Job\JobCode;
use JobBoy\Flow\Domain\FlowManager\StateDiagram\Model\State\State;
use JobBoy\Flow\Domain\FlowManager\StateDiagram\Model\State\StateCode;
use JobBoy\Flow\Domain\FlowManager\StateDiagram\Model\State\StateCollection;
use JobBoy\Flow\Domain\FlowManager\StateDiagram\Model\Transition\EntryStateCollection;
use JobBoy\Flow\Domain\FlowManager\StateDiagram\Model\Transition\Transition;
use JobBoy\Flow\Domain\FlowManager\StateDiagram\Model\Transition\TransitionCollection;
use JobBoy\Flow\Domain\FlowManager\StateDiagram\StateDiagram;

class StateDiagramBuilder implements ParentStateBuilder
{
    const ACTIVE = 'active';

    /** @var Job|null */
    private $job = null;

    /** @var StateCollection */
    private $states;

    /** @var TransitionCollection */
    private $transitions;

    /** @var EntryStateCollection */
    private $entryStates;

    private function __construct()
    {
        $this->states = StateCollection::create();
        $this->transitions = TransitionCollection::create();
        $this->entryStates = EntryStateCollection::create();
    }

    public static function create(): self
    {
        return new self();
    }

    public function createJob(string $code, ?string $name = null): self
    {
        $clone = clone $this;
        $clone->job = Job::create(new JobCode($code), $name ?? $code);

        return $clone;
    }

    public function createState(string $code, ?string $name = null): self
    {
        $state = State::create(new StateCode($code), $name ?? $code);

        $clone = clone $this;

        $clone->states = $clone->states->set($state, self::ACTIVE);

        return $clone;
    }

    public function asEntry(): self
    {
        $clone = clone $this;

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

    public function createStateBuilder(string $code, ?string $name = null): StateBuilder
    {

        return StateBuilder::create(
            $this,
            $code,
            $name ?? $code
        );
    }

    public function build(): StateDiagram
    {
        Assertion::notNull($this->job, 'A Job must be created to build a StateDiagram');

        $stateDiagram = StateDiagram::create($this->job);

        foreach ($this->states->all() as $state) {
            $stateDiagram = $stateDiagram->addState($state);
        }

        foreach ($this->transitions->all() as $transition) {
            $stateDiagram = $stateDiagram->addTransition($transition);
        }

        return $stateDiagram;
    }

    public function _code(): ?StateCode
    {
        return null;
    }

    public function _setState(State $state): ParentStateBuilder
    {
        $clone = clone $this;

        $clone->states = $clone->states->set($state);

        return $clone;
    }

    public function _setTransition(Transition $transition): ParentStateBuilder
    {
        $clone = clone $this;

        $clone->transitions = $clone->transitions->set($transition);

        return $clone;
    }

    public function _tagState(StateCode $stateCode, string $tag): ParentStateBuilder
    {
        $clone = clone $this;
        $clone->states = $clone->states->tag($stateCode, $tag);

        return $clone;
    }
}
