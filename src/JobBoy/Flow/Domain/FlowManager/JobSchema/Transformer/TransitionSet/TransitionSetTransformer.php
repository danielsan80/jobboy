<?php
declare(strict_types=1);

namespace JobBoy\Flow\Domain\FlowManager\JobSchema\Transformer\TransitionSet;

use Assert\Assertion;
use JobBoy\Flow\Domain\FlowManager\JobSchema\JobSchema;
use JobBoy\Flow\Domain\FlowManager\JobSchema\Model\State\State;
use JobBoy\Flow\Domain\FlowManager\JobSchema\Model\State\StateCode;
use JobBoy\Flow\Domain\FlowManager\JobSchema\Model\Transition\Transition;
use JobBoy\Flow\Domain\FlowManager\TransitionLoader\TransitionSet;

class TransitionSetTransformer
{
    public function transform(JobSchema $jobSchema): TransitionSet
    {
        $transitions = [];

        $rootStates = $jobSchema->states()->getChildren(null);

        while ($rootStates) {
            $state = array_shift($rootStates);
            $transitions = array_merge($transitions, $this->getTransitions($jobSchema, $state->code()));
        }

        return new TransitionSet((string)$jobSchema->job()->code(), $transitions);
    }


    private function getTransitions(JobSchema $jobSchema, StateCode $stateCode): array
    {
        $transitions = $jobSchema->transitions()->byStateCode($stateCode);

        $state = $jobSchema->state($stateCode);

        $transitions = array_filter($transitions, function (Transition $transition) use ($jobSchema, $state) {
            if ($this->isAChangeToThisState($transition, $state)) {
                return false;
            }

            if ($this->isAnEntryToThisStateAndHasAParent($transition, $state)) {
                return false;
            }

            if ($this->isAChangeToASuperState($transition, $state, $jobSchema)) {
                return false;
            }

            return true;
        });

        //aggiungere le Transiotions mancanti

        return $transitions;
    }
    private function isAChangeToThisState(Transition $transition, State $state): bool
    {
        if (!$transition->type()->isChange()) {
            return false;
        }

        if ((string)$transition->to() !== (string)$state->code()) {
            return false;
        }

        return true;
    }

    private function isAnEntryToThisStateAndHasAParent(Transition $transition, State $state): bool
    {
        if ($state->parent() === null) {
            return false;
        }

        if (!$transition->type()->isEntry()) {
            return false;
        }

        if ((string)$transition->to() === (string)$state->code()) {
            return false;
        }

        return true;
    }

    private function isAChangeToASuperState(Transition $transition, State $state, JobSchema $jobSchema): bool
    {
        if (!$transition->type()->isChange()) {
            return false;
        }

        $toState = $jobSchema->state($transition->to());

        if (!$jobSchema->states()->hasChildren($toState->code())) {
            return false;
        }

//        $toChildren = $jobSchema->states()->getChildren($toState->code());
//        foreach ($toChildren as $toChild) {
//            $entryTransitions = array_filter($jobSchema->transitions()->byStateCode($toChild->code()), function(Transition $entryTransition) {
//                return $entryTransition->type()->isEntry();
//            });
//
//            $entryTransition = array_pop($entryTransitions);
//
//
//        }

        return true;
    }
}
