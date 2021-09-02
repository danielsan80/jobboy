<?php
declare(strict_types=1);

namespace JobBoy\Flow\Domain\FlowManager\TransitionLoader;

interface TransitionLoader
{
    public function load(TransitionSet $transitionSet): void;
}
