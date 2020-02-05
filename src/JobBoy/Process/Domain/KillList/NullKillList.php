<?php

namespace JobBoy\Process\Domain\KillList;

class NullKillList implements KillList
{

    public function kill(string $processId): void
    {
    }

    public function done(string $processId): void
    {
    }

    public function all(): array
    {
        return [];
    }

    public function toBeKilled(string $processId): bool
    {
        return false;
    }
}