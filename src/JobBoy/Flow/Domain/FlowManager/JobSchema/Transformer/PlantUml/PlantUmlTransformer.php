<?php
declare(strict_types=1);

namespace JobBoy\Flow\Domain\FlowManager\JobSchema\Transformer\PlantUml;

use JobBoy\Flow\Domain\FlowManager\JobSchema\Model\State\StateCode;
use JobBoy\Flow\Domain\FlowManager\JobSchema\Model\Transition\Transition;
use JobBoy\Flow\Domain\FlowManager\JobSchema\JobSchema;

class PlantUmlTransformer
{
    public function transform(JobSchema $jobSchema): string
    {
        $lines = [];

        $rootStates = $jobSchema->states()->getChildren(null);

        while ($rootStates) {
            $state = array_shift($rootStates);
            $lines = array_merge($lines, $this->getStateLines($jobSchema, $state->code()));
            $lines = array_merge($lines, $this->getTransitionLines($jobSchema, $state->code()));
        }




        $lines = array_merge(
            [
                '@startuml',
                'hide empty description',
            ],
            $lines,
            [
                '@enduml',
                '',
            ]
        );

        return implode(PHP_EOL, $lines);
    }

    private function getStateLines(JobSchema $jobSchema, StateCode $code): array
    {
        $state = $jobSchema->state($code);

        $children = $jobSchema->states()->getChildren($code);
        if (!$children) {
            return ['state ' . $state->name()];
        }

        $lines = [];

        foreach ($children as $child) {
            $lines = array_merge($lines, $this->getStateLines($jobSchema, $child->code()));
            $lines = array_merge($lines, $this->getTransitionLines($jobSchema, $child->code()));
        }

        return array_merge(
            ['state ' . $state->name() . ' {'],
            $lines,
            ['}']
        );
    }

    private function getTransitionLines(JobSchema $jobSchema, StateCode $stateCode): array
    {
        $transitions = $jobSchema->transitions()->relatingToState($stateCode);

        $transitions = array_filter($transitions, function (Transition $transition) use ($stateCode) {
            if ($transition->type()->isChange()) {
                if ((string)$transition->to() === (string)$stateCode) {
                    return false;
                }
            }
            return true;
        });

        $lines = [];

        foreach ($transitions as $transition) {
            if ($transition->type()->isEntry()) {
                $state = $jobSchema->state($transition->to());
                $lines[] = '[*] --> ' . $state->name();
            }
            if ($transition->type()->isExit()) {
                $state = $jobSchema->state($transition->from());
                $lines[] = $state->name() . ' --> [*] : ' . $transition->on()->name();
            }
            if ($transition->type()->isChange()) {
                $from = $jobSchema->state($transition->from());
                $to = $jobSchema->state($transition->to());
                $lines[] = $from->name() . ' --> ' . $to->name() . ' : ' . $transition->on()->name();
            }
        }

        return $lines;
    }

}
