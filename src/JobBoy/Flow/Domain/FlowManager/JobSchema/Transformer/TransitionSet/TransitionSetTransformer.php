<?php
declare(strict_types=1);

namespace JobBoy\Flow\Domain\FlowManager\JobSchema\Transformer\TransitionSet;

use Assert\Assertion;
use JobBoy\Flow\Domain\FlowManager\JobSchema\JobSchema;
use JobBoy\Flow\Domain\FlowManager\JobSchema\Model\State\State;
use JobBoy\Flow\Domain\FlowManager\JobSchema\Model\State\StateCode;
use JobBoy\Flow\Domain\FlowManager\JobSchema\Model\Transition\Transition;
use JobBoy\Flow\Domain\FlowManager\JobSchema\Model\Transition\TransitionCollection;
use JobBoy\Flow\Domain\FlowManager\TransitionLoader\Transition as GenericTransition;
use JobBoy\Flow\Domain\FlowManager\TransitionLoader\TransitionSet;

class TransitionSetTransformer
{
    public function transform(JobSchema $jobSchema): TransitionSet
    {
        $transitions = TransitionCollection::create();

        $rootStates = $jobSchema->states()->getChildren(null);

        while ($rootStates) {
            $state = array_shift($rootStates);
            $transitions = $transitions->union($this->getTransitions($jobSchema, $state->code()));
        }

        return new TransitionSet((string)$jobSchema->job()->code(), $this->toGenericTransitions($transitions));
    }


    private function getTransitions(JobSchema $jobSchema, StateCode $stateCode): TransitionCollection
    {
        $state = $jobSchema->state($stateCode);

        $relatingToState = $jobSchema->transitions()->relatingToState($stateCode);

        $transitions = $relatingToState;

        $transitions = $transitions->difference($transitions->changesToState($stateCode));
        $transitions = $transitions->difference($transitions->entriesToState($jobSchema->entryChild($stateCode)));

        $childrenExits = TransitionCollection::create();
        foreach ($jobSchema->states()->getChildren($stateCode) as $child) {
            $childrenExits = $childrenExits->union($transitions->exitsFromState($child));
        }
        $transitions = $transitions->difference($childrenExits);

        $changesFromState = $transitions->changesFromState($stateCode);


        if (!$childrenExits->isEmpty()) {
            $transitions = $transitions->difference($changesFromState);
            $newChangesFromState = TransitionCollection::create();
            foreach ($childrenExits as $childExit) {
                foreach ($changesFromState as $changeFromState) {
                    $newTransition = Transition::change(
                        $childExit,
                        $changeFromState->to(),
                        $changeFromState->on()
                    );
                    $transitions = $transitions->set($newTransition);
                    $newChangesFromState = $newChangesFromState->set($newTransition);
                }
            }
            $changesFromState = $newChangesFromState;
        }

        $changesFromStateToAParentState = $changesFromState->filter(
            function(Transition $transition) use ($jobSchema, $state) {
                return $jobSchema->states()->isParent($transition->to());
            }
        );

        foreach ($changesFromStateToAParentState as $changeFromState) {

            $entryChild = $jobSchema->entryChild($changeFromState->to());

            $newTransition = Transition::change(
                $changesFromState->from()->code(),
                $entryChild->code(),
                $changeFromState->on()
            );

            $transitions = $transitions->remove($changeFromState);
            $transitions = $transitions->set($newTransition);
        }

        foreach ($jobSchema->states()->getChildren($stateCode) as $child) {
            $transitions = $transitions->union($this->getTransitions($jobSchema, $child->code()));
        }

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

    /**
     * @param TransitionCollection $transitions
     * @return GenericTransition[]
     */
    private function toGenericTransitions(TransitionCollection $transitions): array
    {
        $genericTransitions = [];
        foreach ($transitions as $transition) {
            $genericTransitions[] = GenericTransition::fromArray([
                'from' => (string)$transition->from(),
                'to' => (string)$transition->to(),
                'on' => (string)$transition->on()
            ]);
        }
        return $genericTransitions;
    }
}
