<?php
declare(strict_types=1);

namespace JobBoy\Flow\Domain\FlowManager\StateDiagram\Event;

class Event
{
    /** @var EventCode */
    private $code;

    /** @var string */
    private $name;

    private function __construct()
    {
    }

    public static function create(EventCode $code, string $name): self
    {
        $event = new self();

        $event->code = $code;
        $event->name = $name;

        return $event;
    }

    public function code(): EventCode
    {
        return $this->code;
    }

    public function name(): string
    {
        return $this->name;
    }

    public function __toString(): string
    {
        return (string)$this->code;
    }

}
