<?php
declare(strict_types=1);

namespace JobBoy\Flow\Domain\FlowManager\JobSchema\Model\Transition;

use Assert\Assertion;
use JobBoy\Flow\Domain\FlowManager\JobSchema\Model\State\State;
use JobBoy\Flow\Domain\FlowManager\JobSchema\Model\State\StateCode;

class EntryStateCollection
{
    const ROOT = '[root]';

    /** @var array<string,State> */
    private $entryStates = [];

    private function __construct()
    {
    }

    public static function create(): self
    {
        return new self();
    }


    public function set(?StateCode $code, StateCode $childCode): self
    {
        $this->assertEntryStateIsNotSetYet($code);

        $clone = clone $this;
        $clone->entryStates[$this->key($code)] = $childCode;
        return $clone;
    }

    public function has(?StateCode $code): bool
    {
        return isset($this->entryStates[$this->key($code)]);
    }

    public function get(?StateCode $code): ?State
    {
        if (!$this->has($code)) {
            return null;
        }

        return $this->entryStates[$this->key($code)];
    }

    public function assertEntryStateIsNotSetYet(?StateCode $code): void
    {
        Assertion::keyNotExists(
            $this->entryStates,
            $this->key($code),
            sprintf(
                'State "%s" has already an entry state',
                $this->key($code)
            )
        );
    }

    private function key(?StateCode $code): string
    {
        return $code ? (string)$code : self::ROOT;
    }

}
