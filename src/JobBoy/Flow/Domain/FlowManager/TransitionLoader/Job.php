<?php
declare(strict_types=1);

namespace JobBoy\Flow\Domain\FlowManager\TransitionLoader;

use Assert\Assertion;

class Job
{
    private $code;

    public function __construct(string $code)
    {
        Assertion::notBlank($code);
        $this->code = $code;
    }

    public function code(): string
    {
        return $this->code;
    }

    public function __toString()
    {
        return $this->code();
    }

}
