<?php

namespace JobBoy\Process\Domain\KillList;

interface KillList
{
    public function add(string $processId): void;

    public function remove(string $processId): void;

    public function first(): ?string;

    public function all(): array;

    public function inList(string $processId): bool;

}