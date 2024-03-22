<?php
declare(strict_types=1);

namespace JobBoy\Flow\Domain\FlowManager\StateDiagram\Builder;

use JobBoy\Flow\Domain\FlowManager\StateDiagram\Model\State\State;

class StateBuilder
{
    /** @var string */
    private $code;

    /** @var string */
    private $label;

    private function __construct()
    {
    }

    public static function create(): self
    {
        return new self();
    }

    public function withCode(string $code): self
    {
        $clone = clone $this;
        $clone->code = $code;
        return $clone;
    }

    public function withLabel(string $label): self
    {
        $clone = clone $this;
        $clone->label = $label;
        return $clone;
    }

    public function addChild()
    {

    }


    public function build(): State
    {
        return new State();
    }

}
