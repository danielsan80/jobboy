<?php
declare(strict_types=1);

namespace JobBoy\Flow\Domain\FlowManager\TransitionLoader;

use JobBoy\Flow\Domain\FlowManager\TransitionRegistry;

class TransitionLoader
{
    private $transitionRegistry;

    public function __construct(TransitionRegistry $transitionRegistry)
    {
        $this->transitionRegistry = $transitionRegistry;
    }

    public function load(TransitionSet $transitionSet): void
    {
        $job = $transitionSet->job();

        foreach ($transitionSet->transitions() as $transition) {
            $this->transitionRegistry->add($transition->toTransaction($job));
        }
    }

}
