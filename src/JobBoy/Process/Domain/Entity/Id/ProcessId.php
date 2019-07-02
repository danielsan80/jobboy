<?php

namespace JobBoy\Process\Domain\Entity\Id;

use Assert\Assertion;

class ProcessId
{
    private $value;

    public function __construct(string $value)
    {
        Assertion::uuid($value);
        $this->value = $value;
    }

    public function value(): string
    {
        return $this->value;
    }

    public function toScalar(): string
    {
        return $this->value();
    }

    static public function fromScalar($value): self
    {
        return new static($value);
    }

    public function equals(self $id): bool
    {
        return $this->value === $id->value();
    }

    public function __toString(): string
    {
        return $this->toScalar();
    }

}