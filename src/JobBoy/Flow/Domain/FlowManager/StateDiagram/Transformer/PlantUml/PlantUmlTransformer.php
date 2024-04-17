<?php
declare(strict_types=1);

namespace JobBoy\Flow\Domain\FlowManager\StateDiagram\Transformer\PlantUml;

use JobBoy\Flow\Domain\FlowManager\StateDiagram\Model\State\StateCode;
use JobBoy\Flow\Domain\FlowManager\StateDiagram\Model\Transition\Transition;
use JobBoy\Flow\Domain\FlowManager\StateDiagram\StateDiagram;

class PlantUmlTransformer
{
    public function transform(StateDiagram $stateDiagram): string
    {
        $lines = [];

        $rootStates = $stateDiagram->states()->getChildren(null);

        while ($rootStates) {
            $state = array_shift($rootStates);
            $lines = array_merge($lines, $this->getStateLines($stateDiagram, $state->code()));
            $lines = array_merge($lines, $this->getTransitionLines($stateDiagram, $state->code()));
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

    private function getStateLines(StateDiagram $stateDiagram, StateCode $code): array
    {
        $state = $stateDiagram->state($code);

        $children = $stateDiagram->states()->getChildren($code);
        if (!$children) {
            return ['state ' . $state->name()];
        }

        $lines = [];

        foreach ($children as $child) {
            $lines = array_merge($lines, $this->getStateLines($stateDiagram, $child->code()));
            $lines = array_merge($lines, $this->getTransitionLines($stateDiagram, $child->code()));
        }

        return array_merge(
            ['state ' . $state->name() . ' {'],
            $lines,
            ['}']
        );
    }

    private function getTransitionLines(StateDiagram $stateDiagram, StateCode $parentCode): array
    {
        $transitions = $stateDiagram->transitions()->byStateCode($parentCode);

        $lines = [];

        foreach ($transitions as $transition) {
            if ($transition->type()->isEntry()) {
                $state = $stateDiagram->state($transition->to());
                $lines[] = '[*] --> ' . $state->name();
            }
            if ($transition->type()->isExit()) {
                $state = $stateDiagram->state($transition->from());
                $lines[] = $state->name() . ' --> [*] : ' . $transition->on()->name();
            }
            if ($transition->type()->isChange()) {
                $from = $stateDiagram->state($transition->from());
                $to = $stateDiagram->state($transition->to());
                $lines[] = $from->name() . ' --> ' . $to->name() . ' : ' . $transition->event()->name();
            }
        }

        return $lines;
    }

}
