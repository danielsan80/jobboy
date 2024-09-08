<?php
declare(strict_types=1);

namespace JobBoy\Flow\Domain\FlowManager\JobSchema\Builder;

use Assert\Assertion;
use JobBoy\Flow\Domain\FlowManager\JobSchema\Model\Event\Event;
use JobBoy\Flow\Domain\FlowManager\JobSchema\Model\Job\Job;
use JobBoy\Flow\Domain\FlowManager\JobSchema\Model\State\State;
use JobBoy\Flow\Domain\FlowManager\JobSchema\Model\State\StateCode;
use JobBoy\Flow\Domain\FlowManager\JobSchema\Model\State\StateCollection;
use JobBoy\Flow\Domain\FlowManager\JobSchema\Model\State\StateStack;
use JobBoy\Flow\Domain\FlowManager\JobSchema\Model\Transition\EntryStateCollection;
use JobBoy\Flow\Domain\FlowManager\JobSchema\Model\Transition\Transition;
use JobBoy\Flow\Domain\FlowManager\JobSchema\Model\Transition\TransitionCollection;
use JobBoy\Flow\Domain\FlowManager\JobSchema\JobSchema;

class JobSchemaBuilder
{

    /** @var Job|null */
    private $job = null;


    /** @var StateCollection */
    private $states;

    /** @var TransitionCollection */
    private $transitions;

    /** @var EntryStateCollection */
    private $entryStates;

    /** @var StateStack */
    private $stack;

    private function __construct()
    {
        $this->states = StateCollection::create();
        $this->transitions = TransitionCollection::create();
        $this->entryStates = EntryStateCollection::create();
        $this->stack = StateStack::create();
    }

    public static function create(): self
    {
        return new self();
    }

    public function setJob(string $code, ?string $name = null): self
    {
        $clone = clone $this;
        $clone->job = Job::fromString($code, $name);

        return $clone;
    }

    public function createState(string $code, ?string $name = null): self
    {
        $state = State::fromString($code, $name);

        $parent = $this->stack->parent();

        if ($parent) {
            $state = $state->setParent($parent->code());
        }

        $clone = clone $this;

        $clone->states = $clone->states->set($state);
        $clone->stack = $clone->stack->replace($state);

        return $clone;
    }

    public function asEntry(): self
    {
        $clone = clone $this;

        $currentState = $clone->stack->current();

        $clone->entryStates = $clone->entryStates->set($currentState->parent(), $currentState->code());

        $transition = Transition::entry($currentState->code());
        $clone->transitions = $clone->transitions->set($transition);

        return $clone;
    }

    public function asExit(string $on, ?string $name = null): self
    {
        $clone = clone $this;

        $currentState = $clone->stack->current();

        $transition = Transition::exit(
            $currentState->code(),
            Event::fromString($on, $name)
        );

        $clone->transitions = $clone->transitions->set($transition);

        return $clone;
    }

    public function whichComesFrom(string $state, string $on, ?string $onName = null): self
    {
        $clone = clone $this;

        $from = StateCode::create($state);

        Assertion::true($clone->states->has($from), sprintf('State %s not found', $state));

        $currentState = $clone->stack->current();
        $to = $currentState->code();

        $transition = Transition::change($from, $to, Event::fromString($on, $onName));
        $clone->transitions = $clone->transitions->set($transition);

        return $clone;
    }

    public function whichGoesTo(string $state, string $on, ?string $onName = null): self
    {
        $clone = clone $this;

        $to = StateCode::create($state);

        Assertion::true($clone->states->has($to), sprintf('State %s not found', $state));

        $currentState = $clone->stack->current();
        $from = $currentState->code();

        $transition = Transition::change($from, $to, Event::fromString($on, $onName));
        $clone->transitions = $clone->transitions->set($transition);

        return $clone;
    }

    public function createChange(string $from, string $to, string $on, ?string $onName = null): self
    {
        $clone = clone $this;

        $from = StateCode::create($from);
        $to = StateCode::create($to);

        Assertion::true($clone->states->has($from), sprintf('State %s not found', $from));
        Assertion::true($clone->states->has($to), sprintf('State %s not found', $to));

        $transition = Transition::change($from, $to, Event::fromString($on, $onName));
        $clone->transitions = $clone->transitions->set($transition);

        return $clone;
    }

    public function open(): self
    {
        $clone = clone $this;

        $clone->stack = $clone->stack->open();

        return $clone;
    }

    public function close(): self
    {
        $clone = clone $this;

        $clone->stack = $clone->stack->close();

        return $clone;
    }

    public function build(): JobSchema
    {
        Assertion::notNull($this->job, 'A Job must be created to build a JobSchema');

        $jobSchema = JobSchema::create($this->job);

        foreach ($this->states->all() as $state) {
            $jobSchema = $jobSchema->addState($state);
        }

        foreach ($this->transitions->all() as $transition) {
            $jobSchema = $jobSchema->addTransition($transition);
        }

        return $jobSchema;
    }


}
