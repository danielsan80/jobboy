<?php
declare(strict_types=1);

namespace JobBoy\Flow\Domain\FlowManager\StateDiagram\Model\State;

use Assert\Assertion;

class StateCode
{
    /** @var string */
    private $value;

    private function __construct()
    {
    }

    public static function create(string $value): self
    {
        Assertion::notBlank($value, 'StateCode cannot be blank');

        $code = new self();
        $code->value = $value;

        return $code;

    }

    public function __toString(): string
    {
        return $this->value;
    }

}
