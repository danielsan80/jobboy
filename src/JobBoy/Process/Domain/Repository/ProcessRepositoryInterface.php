<?php

namespace JobBoy\Process\Domain\Repository;

use JobBoy\Process\Domain\Entity\Id\ProcessId;
use JobBoy\Process\Domain\Entity\Process;
use JobBoy\Process\Domain\ProcessStatus;

interface ProcessRepositoryInterface
{
    const DEFAULT_STALE_DAYS = 90;

    public function add(Process $process): void;
    public function remove(Process $process): void;

    public function byId(ProcessId $id): ?Process;
    public function all(?int $start = null, ?int $length = null): array;

    public function handled(?int $start = null, ?int $length = null): array;
    public function stale(?\DateTimeImmutable $until = null, ?int $start = null, ?int $length = null): array;
    public function byStatus(ProcessStatus $status, ?int $start = null, ?int $length = null): array;


}