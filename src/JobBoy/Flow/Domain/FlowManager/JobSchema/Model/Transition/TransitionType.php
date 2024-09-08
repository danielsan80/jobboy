<?php
declare(strict_types=1);

namespace JobBoy\Flow\Domain\FlowManager\JobSchema\Model\Transition;

use Assert\Assertion;

class TransitionType
{
    const ENTRY = 'entry';
    const EXIT = 'exit';
    const CHANGE = 'change';

    /** @var string */
    private $value;

    private function __construct(string $value)
    {
        Assertion::inArray($value, [self::ENTRY, self::EXIT, self::CHANGE], 'Invalid transition type');
        $this->value = $value;
    }

    public static function entry(): self
    {
        return new self(self::ENTRY);
    }

    public static function exit(): self
    {
        return new self(self::EXIT);
    }

    public static function change(): self
    {
        return new self(self::CHANGE);
    }

    public function isEntry(): bool
    {
        return $this->value === self::ENTRY;
    }

    public function isExit(): bool
    {
        return $this->value === self::EXIT;
    }

    public function isChange(): bool
    {
        return $this->value === self::CHANGE;
    }

    public function __toString(): string
    {
        return $this->value;
    }

}
