<?php
declare(strict_types=1);

namespace JobBoy\Flow\Domain\FlowManager\StateDiagram\Model\State;

use Assert\Assertion;

class StateCode
{
    /** @var string */
    private $value;

    public function __construct(string $value)
    {
        Assertion::notBlank($value, 'StateCode cannot be blank');
        $this->value = $value;
    }

    public function __toString(): string
    {
        return $this->value;
    }

}
