<?php
declare(strict_types=1);

namespace JobBoy\Flow\Domain\FlowManager\StateDiagram\Event;

use Assert\Assertion;

class EventCode
{
    /** @var string */
    private $value;

    public function __construct(string $value)
    {
        Assertion::notBlank($value, 'EventCode cannot be blank');

        $this->value = $value;
    }


    public function __toString(): string
    {
        return $this->value;
    }

}
