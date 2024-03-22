<?php
declare(strict_types=1);

namespace JobBoy\Flow\Domain\FlowManager\StateDiagram\Model\State;

class State
{
    /** @var StateCode|null */
    private $parent;

    /** @var StateCode */
    private $code;

    /** @var string */
    private $name;

    private function __construct(StateCode $code, string $name)
    {
        $this->parent = null;
        $this->code = $code;
        $this->name = $name;
    }

    public static function create(StateCode $code, string $name): self
    {
        return new self($code, $name);
    }

    public function setParent(StateCode $parent): self
    {
        $clone = clone $this;
        $clone->parent = $parent;

        return $clone;
    }

    public function parent(): ?StateCode
    {
        return $this->parent;
    }

    public function isRoot(): bool
    {
        return null === $this->parent;
    }

    public function code(): StateCode
    {
        return $this->code;
    }

    public function name(): string
    {
        return $this->name;
    }

    public function __toString(): string
    {
        return (string)$this->code;
    }

}
