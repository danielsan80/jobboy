<?php

namespace Tests\JobBoy\Process\Test\Domain\Event;

use JobBoy\Process\Domain\Event\Message\HasMessageInterface;

class SpiedEvent
{
    private $class;
    private $text;
    private $parameters;

    public function __construct($event)
    {
        $this->class = get_class($event);
        if ($event instanceof HasMessageInterface) {
            $this->text = $event->message()->text();
            $this->parameters = $event->message()->parameters();
        }
    }

    public function toArray(): array
    {
        return [
            'class' => $this->class,
            'text' => $this->text,
            'parameters' => $this->parameters,
        ];
    }
}