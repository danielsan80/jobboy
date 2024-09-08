<?php
declare(strict_types=1);

namespace JobBoy\Flow\Domain\FlowManager\JobSchema;

use JobBoy\Flow\Domain\FlowManager\JobSchema\Model\Job\Job;
use JobBoy\Flow\Domain\FlowManager\JobSchema\Model\State\State;
use JobBoy\Flow\Domain\FlowManager\JobSchema\Model\State\StateCode;
use JobBoy\Flow\Domain\FlowManager\JobSchema\Model\State\StateCollection;
use JobBoy\Flow\Domain\FlowManager\JobSchema\Model\Transition\EntryStateCollection;
use JobBoy\Flow\Domain\FlowManager\JobSchema\Model\Transition\Transition;
use JobBoy\Flow\Domain\FlowManager\JobSchema\Model\Transition\TransitionCollection;
use JobBoy\Flow\Domain\FlowManager\JobSchema\Transformer\PlantUml\PlantUmlTransformer;

/**
 * @psalm-type StateKey = string
 * @psalm-type TransitionKey = string
 * @psalm-type RootKey = self::ROOT
 */
class JobSchema
{
    /** @var Job */
    private $job;

    /** @var StateCollection */
    private $states;

    /** @var TransitionCollection */
    private $transitions;

    /** @var EntryStateCollection */
    private $entryStates;


    private function __construct(Job $job)
    {
        $this->job = $job;
        $this->states = StateCollection::create();
        $this->transitions = TransitionCollection::create();
        $this->entryStates = EntryStateCollection::create();
    }

    public static function create(Job $job): self
    {
        return new self($job);
    }


    public function job(): Job
    {
        return $this->job;
    }

    public function addState(State $state): self
    {
        $clone = clone $this;
        $clone->states = $clone->states->set($state);

        return $clone;
    }

    public function state(StateCode $code): ?State
    {
        return $this->states->get($code);
    }

    public function addTransition(Transition $transition): self
    {
        $this->transitions->assertTransitionIsNotSetYet($transition);

        if ($transition->type()->isEntry()) {

            $this->states->assertStateIsSet($transition->to());

            $parent = $this->states->getParent($transition->to());

            $this->entryStates->assertEntryStateIsNotSetYet($parent ? $parent->code() : null);

            $clone = clone $this;

            $clone->entryStates = $clone->entryStates->set($parent ? $parent->code() : null, $transition->to());

            $clone->transitions = $clone->transitions->set($transition);

            return $clone;
        }

        if ($transition->type()->isExit()) {
            $this->states->assertStateIsSet($transition->from());

            $clone = clone $this;
            $clone->transitions = $clone->transitions->set($transition);

            return $clone;
        }

        if ($transition->type()->isChange()) {
            $this->states->assertStateIsSet($transition->from());
            $this->states->assertStateIsSet($transition->to());

            $this->states->assertStatesHaveSameParent($transition->from(), $transition->to());

            $clone = clone $this;
            $clone->transitions = $clone->transitions->set($transition);

            return $clone;
        }
    }

    public function transitions(): TransitionCollection
    {
        return $this->transitions;
    }

    public function states(): StateCollection
    {
        return $this->states;
    }

    public function toPlantUml(): string
    {
        $transformer = new PlantUmlTransformer();

        return $transformer->transform($this);
    }

}
