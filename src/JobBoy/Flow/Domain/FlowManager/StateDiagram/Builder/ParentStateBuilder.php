<?php
declare(strict_types=1);

namespace JobBoy\Flow\Domain\FlowManager\StateDiagram\Builder;

use JobBoy\Flow\Domain\FlowManager\StateDiagram\Model\State\State;
use JobBoy\Flow\Domain\FlowManager\StateDiagram\Model\State\StateCode;
use JobBoy\Flow\Domain\FlowManager\StateDiagram\Model\Transition\Transition;

interface ParentStateBuilder
{
    public function _code(): ?StateCode;

    public function _setState(State $state): self;

    public function _tagState(StateCode $stateCode, string $tag): self;

    public function _setTransition(Transition $transition): self;

}
