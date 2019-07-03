<?php

namespace JobBoy\Process\Domain\Repository\Infrastructure\InMemory;

use Dan\Clock\Domain\Clock;
use JobBoy\Process\Domain\Entity\Id\ProcessId;
use JobBoy\Process\Domain\Entity\Process;
use JobBoy\Process\Domain\Repository\ProcessRepositoryInterface;

class ProcessRepository implements ProcessRepositoryInterface
{
    protected $processes = [];

    public function byId(ProcessId $id): ?Process
    {
        if (!array_key_exists((string)$id,$this->processes)) {
            return null;
        }
    }

    public function all(?int $start = null, ?int $length = null): array
    {
        $processes = array_values($this->processes);

        usort($processes, ProcessUtil::getUpdatedAtCompareFunction());

        return ProcessUtil::slice($this->processes, $start, $length);
    }

    public function handled(?string $handledFor = null, ?int $limit = null): array
    {
        if (!$handledFor) {
            $handledFor = ProcessRepositoryInterface::DEFAULT_HANDLED_TIMEOUT;
        }

        $handledSince = Clock::createDateTimeImmutable('- ' . $handledFor);

        $processes = array_values($this->processes);
        array_filter($processes, function(Process $process) use ($handledSince) {
            return $process->isHandled() && $process->handledAt() < $handledSince;
        });

        if ($limit!==null) {
            return array_slice($processes,0, $limit);
        }

        return $processes;
    }

    public function active(?int $limit = null): array
    {
        // TODO: Implement active() method.
    }

    public function evolving(?int $limit = null): array
    {
        // TODO: Implement evolving() method.
    }

    public function starting(?int $limit = null): array
    {
        // TODO: Implement starting() method.
    }

    public function add(Process $process): void
    {
        // TODO: Implement add() method.
    }

    public function removeUntil(\DateTimeImmutable $date): void
    {
        // TODO: Implement removeUntil() method.
    }
}