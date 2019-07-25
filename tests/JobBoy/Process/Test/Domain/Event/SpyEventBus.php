<?php

namespace Tests\JobBoy\Process\Test\Domain\Event;

use JobBoy\Process\Domain\Event\EventBusInterface;
use JobBoy\Process\Domain\Event\EventListenerInterface;

class SpyEventBus implements EventBusInterface
{
    protected $events = [];

    public function subscribe(EventListenerInterface $eventListener): void
    {
    }

    public function unsubscribe(EventListenerInterface $eventListener): void
    {
    }

    public function publish($event): void
    {
        $this->events[] = new SpiedEvent($event);
    }

    public function getSpiedEvents(): array
    {
        return $this->events;
    }
}