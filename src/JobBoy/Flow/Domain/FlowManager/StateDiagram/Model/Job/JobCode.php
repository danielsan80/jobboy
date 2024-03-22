<?php
declare(strict_types=1);

namespace JobBoy\Flow\Domain\FlowManager\StateDiagram\Model\Job;

use Assert\Assertion;

class JobCode
{
    /** @var string */
    private $value;

    public function __construct(string $value)
    {
        Assertion::notBlank($value, 'JobCode cannot be blank');

        $this->value = $value;
    }


    public function __toString(): string
    {
        return $this->value;
    }

}
