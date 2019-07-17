<?php

namespace JobBoy\Process\Domain\Event;

class NullEventBus implements EventBusInterface
{

    public function subscribe(EventListenerInterface $eventListener): void
    {
    }

    public function unsubscribe(EventListenerInterface $eventListener): void
    {
    }

    public function publish($event): void
    {
    }
}