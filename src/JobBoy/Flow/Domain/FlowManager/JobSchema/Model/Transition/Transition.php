<?php
declare(strict_types=1);

namespace JobBoy\Flow\Domain\FlowManager\JobSchema\Model\Transition;

use JobBoy\Flow\Domain\FlowManager\JobSchema\Model\Event\Event;
use JobBoy\Flow\Domain\FlowManager\JobSchema\Model\State\StateCode;

class Transition
{
    /** @var TransitionType */
    private $type;

    /** @var StateCode|null */
    private $from;

    /** @var StateCode|null */
    private $to;

    /** @var Event|null */
    private $on;


    private function __construct(TransitionType $type, ?StateCode $from, ?StateCode $to, ?Event $on)
    {
        $this->type = $type;
        $this->from = $from;
        $this->to = $to;
        $this->on = $on;
    }

    public static function entry(StateCode $to): self
    {
        return new self(TransitionType::entry(), null, $to, null);
    }

    public static function exit(StateCode $from, Event $on): self
    {
        return new self(TransitionType::exit(), $from, null, $on);
    }

    public static function change(StateCode $from, StateCode $to, Event $on): self
    {
        return new self(TransitionType::change(), $from, $to, $on);
    }

    public function type(): TransitionType
    {
        return $this->type;
    }

    public function from(): ?StateCode
    {
        return $this->from;
    }

    public function to(): ?StateCode
    {
        return $this->to;
    }

    public function on(): ?Event
    {
        return $this->on;
    }

    public function __toString(): string
    {
        if ($this->type->isEntry()) {
            return '⚫ -> ' . $this->to;
        }

        if ($this->type->isExit()) {
            return $this->from . ':' . $this->on . ' -> ⚪';
        }

        if ($this->type->isChange()) {
            return $this->from . ':' . $this->on . ' -> ' . $this->to;
        }

        return '<this code should not be executed>';
    }


}
