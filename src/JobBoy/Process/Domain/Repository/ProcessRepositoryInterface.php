<?php

namespace JobBoy\Process\Domain\Repository;

use JobBoy\Process\Domain\Entity\Id\ProcessId;
use JobBoy\Process\Domain\Entity\Process;

interface ProcessRepositoryInterface
{
    const DEFAULT_HANDLED_TIMEOUT = '20 minutes';

    public function byId(ProcessId $id): ?Process;
    public function all(?int $start = null, ?int $length = null): array;

    public function handled(?string $handledFor = null, ?int $limit = null): array;

    public function active(?int $limit = null): array;
    public function evolving(?int $limit = null): array;
    public function starting(?int $limit = null): array;

    public function add(Process $process): void;
    public function removeUntil(\DateTimeImmutable $date): void;

}