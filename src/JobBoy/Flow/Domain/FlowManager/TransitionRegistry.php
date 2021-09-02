<?php

namespace JobBoy\Flow\Domain\FlowManager;

interface TransitionRegistry
{
    public function add(Transition $transition): void;

    public function getEntry(string $job): Transition;

    public function get(Node $from, string $on): Transition;
}