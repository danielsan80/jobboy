<?php

namespace JobBoy\Process\Domain\PauseControl;

class NullPauseControl implements PauseControl
{

    public function pause(): void
    {
    }

    public function unpause(): void
    {
    }

    public function isPaused(): bool
    {
        return false;
    }

    public function resolveRequests(): void
    {
    }
}