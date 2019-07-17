<?php

namespace JobBoy\Process\Domain\Event;

abstract class AbstractEventListener implements EventListenerInterface
{

    protected function support($event): bool
    {

    }


    public function handle($event): void
    {
        if (!$this->support($event)) {
            return;
        }

        $this->doHandle($event);
    }

    abstract protected function doHandle($event): void;
}