<?php

namespace JobBoy\Process\Domain\Event;

interface EventListenerInterface
{
    public function handle($event): void;
}