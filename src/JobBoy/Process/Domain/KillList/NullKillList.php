<?php

namespace JobBoy\Process\Domain\KillList;

class NullKillList implements KillList
{

    public function add(string $processId): void
    {
    }

    public function remove(string $processId): void
    {
    }

    public function first(): ?string
    {
        return null;
    }

    public function all(): array
    {
        return [];
    }

    public function inList(string $processId): bool
    {
        return false;
    }

}