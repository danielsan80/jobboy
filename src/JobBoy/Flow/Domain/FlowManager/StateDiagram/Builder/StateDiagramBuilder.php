<?php
declare(strict_types=1);

namespace JobBoy\Flow\Domain\FlowManager\StateDiagram\Builder;

use Assert\Assertion;
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

        $clone->states = $clone->states->set($state);

        return $clone;
    }

    public function createEntryState(string $code, ?string $name = null): self
    {

        $clone = clone $this;

        $state = State::create(new StateCode($code), $name ?? $code);
        $clone->states = $clone->states->set($state);

        $clone->entryStates = $clone->entryStates->set(null, $state->code());

        $transition = Transition::entry($state->code());
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

}
