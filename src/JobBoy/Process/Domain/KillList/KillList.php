<?php

namespace JobBoy\Process\Domain\KillList;

interface KillList
{
    public function kill(string $processId): void;

    public function done(string $processId): void;

    public function all(): array;

    public function toBeKilled(string $processId): bool;

}