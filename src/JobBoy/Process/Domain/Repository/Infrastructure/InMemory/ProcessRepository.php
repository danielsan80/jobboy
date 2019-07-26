<?php

namespace JobBoy\Process\Domain\Repository\Infrastructure\InMemory;

use JobBoy\Clock\Domain\Clock;
use JobBoy\Process\Domain\Entity\Id\ProcessId;
use JobBoy\Process\Domain\Entity\Process;
use JobBoy\Process\Domain\ProcessStatus;
use JobBoy\Process\Domain\Repository\Infrastructure\Util\ProcessRepositoryUtil;
use JobBoy\Process\Domain\Repository\ProcessRepositoryInterface;

class ProcessRepository implements ProcessRepositoryInterface
{
    protected $processes = [];

    public function add(Process $process): void
    {
        $this->processes[(string)$process->id()] = $process;
    }

    public function remove(Process $process): void
    {
        if (array_key_exists((string)$process->id(), $this->processes)) {
            unset($this->processes[(string)$process->id()]);
        }
    }

    public function byId(ProcessId $id): ?Process
    {
        if (!array_key_exists((string)$id, $this->processes)) {
            return null;
        }

        return $this->processes[(string)$id];
    }

    public function all(?int $start = null, ?int $length = null): array
    {
        $processes = array_values($this->processes);

        $processes = ProcessRepositoryUtil::sort($processes);
        $processes = ProcessRepositoryUtil::slice($processes, $start, $length);

        return $processes;
    }

    public function handled(?int $start = null, ?int $length = null): array
    {
        $processes = $this->processes;
        $processes = array_filter($processes, function (Process $process) {
            return $process->isHandled();
        });

        $processes = ProcessRepositoryUtil::sort($processes);
        $processes = ProcessRepositoryUtil::slice($processes, $start, $length);
        return $processes;
    }

    public function byStatus(ProcessStatus $status, ?int $start = null, ?int $length = null): array
    {
        $processes = $this->processes;
        $processes = array_filter($processes, function (Process $process) use ($status) {
            return $process->status()->equals($status);
        });

        $processes = ProcessRepositoryUtil::sort($processes);
        $processes = ProcessRepositoryUtil::slice($processes, $start, $length);
        return $processes;

    }

    public function stale(?\DateTimeImmutable $until = null, ?int $start = null, ?int $length = null): array
    {
        if (!$until) {
            $until = ProcessRepositoryUtil::aFewDaysAgo(self::DEFAULT_STALE_DAYS);
        }

        $processes = $this->processes;
        $processes = array_filter($processes, function(Process $process) use ($until) {
            return $process->updatedAt()<$until;
        });

        $processes = ProcessRepositoryUtil::sort($processes);
        $processes = ProcessRepositoryUtil::slice($processes, $start, $length);
        return $processes;
    }
}