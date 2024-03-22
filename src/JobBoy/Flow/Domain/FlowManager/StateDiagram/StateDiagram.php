<?php
declare(strict_types=1);

namespace JobBoy\Flow\Domain\FlowManager\StateDiagram;

use Assert\Assertion;
use JobBoy\Flow\Domain\FlowManager\StateDiagram\Model\Job\Job;
use JobBoy\Flow\Domain\FlowManager\StateDiagram\Model\State\State;
use JobBoy\Flow\Domain\FlowManager\StateDiagram\Model\State\StateCode;
use JobBoy\Flow\Domain\FlowManager\StateDiagram\Model\Transition\Transition;

/**
 * @psalm-type StateKey = string
 * @psalm-type TransitionKey = string
 * @psalm-type RootKey = self::ROOT
 */
class StateDiagram
{
    const ROOT = '[root]';

    /** @var Job */
    private $job;

    /** @var array<StateKey, State> */
    private $states = [];

    /** @var array<TransitionKey, Transition> */
    private $transitions = [];


    /** @var array<RootKey|StateKey, StateCode> */
    private $entryStateCodes = [];


    private function __construct(Job $job)
    {
        $this->job = $job;
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
        Assertion::notKeyExists($this->states, (string)$state, sprintf('State "%s" already added', (string)$state));

        if ($state->parent()) {
            Assertion::keyExists($this->states, (string)$state->parent(), sprintf('Parent state "%s" not added yet', (string)$state->parent()));
        }

        $clone = clone $this;
        $clone->states[$this->key($state)] = $state;

        return $clone;
    }

    public function state(StateCode $code): ?State
    {
        if (isset($this->states[$this->key($code)])) {
            return $this->states[$this->key($code)];
        }
        return null;
    }

    public function entryState(?StateCode $code): ?State
    {
        $key = $this->key($code);

        if (!isset($this->entryStateCodes[$key])) {
            return null;
        }

        return $this->state($this->entryStateCodes[$key]);
    }

    public function addTransition(Transition $transition): self
    {
        $this->assertTransitionDoesNotExist($transition);

        if ($transition->type()->isEntry()) {

            $this->assertStateExists($transition->to());

            $toParentCode = $this->state($transition->to())->parent();
            $this->assertEntryStateDoesNotSetYet($toParentCode);

            $clone = clone $this;

            $clone->entryStateCodes[$this->key($toParentCode)] = $transition->to();
            $clone->transitions[(string)$transition] = $transition;


        }
    }

    private function key($state): string
    {
        if ($state instanceof State) {
            $state = $state->code();
        }

        return $state ? (string)$state : self::ROOT;
    }

    private function assertTransitionDoesNotExist(Transition $transition): void
    {
        Assertion::notKeyExists($this->transitions, (string)$transition, sprintf('Transition "%s" already added', (string)$transition));
    }

    private function assertStateExists(StateCode $code): void
    {
        Assertion::notNull($this->state($code), sprintf('State "%s" does not exist', (string)$code));
    }

    private function assertEntryStateDoesNotSetYet(?StateCode $code): void
    {
        $entryState = $this->entryState($code);
        Assertion::null($entryState, sprintf('Entry state for "%s" already set to "%s"', $code?(string)$code:self::ROOT, (string)$entryState));
    }
}
