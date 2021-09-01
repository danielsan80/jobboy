<?php
declare(strict_types=1);

namespace JobBoy\Flow\Domain\FlowManager\TransitionLoader;

interface TransitionSetProvider
{
    public function get(): TransitionSet;
}
