<?php

namespace JobBoy\Process\Domain\PauseControl;

interface PauseControl
{
    public function pause(): void;

    public function unpause(): void;

    public function resolveRequests(): void;

    public function isPaused(): bool;
}