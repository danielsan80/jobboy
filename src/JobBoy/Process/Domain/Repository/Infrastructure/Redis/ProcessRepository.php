<?php

namespace JobBoy\Process\Domain\Repository\Infrastructure\Redis;

use JobBoy\Process\Domain\Entity\Id\ProcessId;
use JobBoy\Process\Domain\Entity\Process;
use JobBoy\Process\Domain\ProcessStatus;
use JobBoy\Process\Domain\Repository\Infrastructure\Util\ProcessRepositoryUtil;
use JobBoy\Process\Domain\Repository\ProcessRepositoryInterface;

class ProcessRepository implements ProcessRepositoryInterface
{
    const DEFAULT_NAMESPACE = 'jobboy-processes';

    /** @var \Redis */
    protected $redis;

    /** @var string|null */
    protected $namespace;

    public function __construct(\Redis $redis, ?string $namespace = null)
    {
        $this->redis = $redis;
        if (!$namespace) {
            $namespace = self::DEFAULT_NAMESPACE;
        }
        $this->namespace = $namespace;
    }

    public function add(Process $process): void
    {
        $this->redis->hset($this->namespace, (string)$process->id(), $process);
    }

    public function remove(Process $process): void
    {
        $this->redis->hDel($this->namespace, (string)$process->id());
    }


    public function byId(ProcessId $id): ?Process
    {
        $value = $this->redis->hget($this->namespace, (string)$id);

        if ($value===false) {
            return null;
        }

        return $value;
    }

    public function all(?int $start = null, ?int $length = null): array
    {
        $processes = $this->redis->hGetAll($this->namespace);

        $processes = array_values($processes);

        $processes = ProcessRepositoryUtil::sort($processes);
        $processes = ProcessRepositoryUtil::slice($processes, $start, $length);

        return $processes;
    }

    public function handled(?int $start = null, ?int $length = null): array
    {
        $processes = $this->redis->hGetAll($this->namespace);

        $processes = array_filter($processes, function (Process $process) {
            return $process->isHandled();
        });

        $processes = ProcessRepositoryUtil::sort($processes);
        $processes = ProcessRepositoryUtil::slice($processes, $start, $length);
        return $processes;
    }

    public function byStatus(ProcessStatus $status, ?int $start = null, ?int $length = null): array
    {
        $processes = $this->redis->hGetAll($this->namespace);
        $processes = array_filter($processes, function (Process $process) use ($status) {
            return $process->status()->equals($status);
        });

        $processes = ProcessRepositoryUtil::sort($processes);
        $processes = ProcessRepositoryUtil::slice($processes, $start, $length);
        return $processes;

    }


    public function stale(?\DateTimeImmutable $until = null, ?int $start = null, ?int $length = null): array
    {
        $processes = $this->redis->hGetAll($this->namespace);
        $processes = array_filter($processes, function (Process $process) use ($until) {
            return $process->updatedAt() < $until;
        });

        $processes = ProcessRepositoryUtil::sort($processes);
        $processes = ProcessRepositoryUtil::slice($processes, $start, $length);
        return $processes;
    }


}