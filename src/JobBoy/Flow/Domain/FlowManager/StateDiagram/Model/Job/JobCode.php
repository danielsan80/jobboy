<?php
declare(strict_types=1);

namespace JobBoy\Flow\Domain\FlowManager\StateDiagram\Model\Job;

use Assert\Assertion;

class JobCode
{
    /** @var string */
    private $value;

    private function __construct()
    {

    }

    public static function create(string $value): self
    {
        Assertion::notBlank($value, 'JobCode cannot be blank');

        $code = new self();
        $code->value = $value;

        return $code;
    }


    public function __toString(): string
    {
        return $this->value;
    }

}
