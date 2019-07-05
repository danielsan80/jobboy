<?php

namespace JobBoy\Process\Domain\Repository\Infrastructure\Redis;

use Assert\Assertion;
use JobBoy\Process\Domain\Entity\Id\ProcessId;
use JobBoy\Process\Domain\Entity\Process;
use JobBoy\Process\Domain\Entity\Infrastructure\Redis\Process as RedisProcess;
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

    protected $touchCallback;

    public function __construct(\Redis $redis, ?string $namespace = null)
    {
        $this->redis = $redis;
        if (!$namespace) {
            $namespace = self::DEFAULT_NAMESPACE;
        }
        $this->namespace = $namespace;

        $this->touchCallback = function(Process $process) {
            $this->onTouch($process);
        };
    }

    protected function onTouch(Process $process) {
        $this->set($process);
    }

    public function add(Process $process): void
    {
        Assertion::isInstanceOf($process, RedisProcess::class);
        $this->set($process);
    }

    public function remove(Process $process): void
    {
        $this->unset($process);
    }


    public function byId(ProcessId $id): ?Process
    {
        return $this->get((string)$id);
    }

    public function all(?int $start = null, ?int $length = null): array
    {
        $processes = $this->getAll();

        $processes = array_values($processes);

        $processes = ProcessRepositoryUtil::sort($processes);
        $processes = ProcessRepositoryUtil::slice($processes, $start, $length);

        return $processes;
    }

    public function handled(?int $start = null, ?int $length = null): array
    {
        $processes = $this->getAll();

        $processes = array_filter($processes, function (Process $process) {
            return $process->isHandled();
        });

        $processes = ProcessRepositoryUtil::sort($processes);
        $processes = ProcessRepositoryUtil::slice($processes, $start, $length);
        return $processes;
    }

    public function byStatus(ProcessStatus $status, ?int $start = null, ?int $length = null): array
    {
        $processes = $this->getAll();
        $processes = array_filter($processes, function (Process $process) use ($status) {
            return $process->status()->equals($status);
        });

        $processes = ProcessRepositoryUtil::sort($processes);
        $processes = ProcessRepositoryUtil::slice($processes, $start, $length);
        return $processes;

    }


    public function stale(?\DateTimeImmutable $until = null, ?int $start = null, ?int $length = null): array
    {
        $processes = $this->getAll();
        $processes = array_filter($processes, function (Process $process) use ($until) {
            return $process->updatedAt() < $until;
        });

        $processes = ProcessRepositoryUtil::sort($processes);
        $processes = ProcessRepositoryUtil::slice($processes, $start, $length);
        return $processes;
    }

    protected function set(RedisProcess $process): void
    {
        $process->removeTouchCallback($this->touchCallback);
        $this->redis->hset($this->namespace, (string)$process->id(), $process);
        $process->addTouchCallback($this->touchCallback);
    }

    protected function get(string $id): ?RedisProcess
    {
        $process = $this->redis->hget($this->namespace, $id);

        if ($process===false) {
            return null;
        }

        $process->addTouchCallback($this->touchCallback);

        return $process;
    }

    protected function unset(RedisProcess $process): void
    {
        $this->redis->hDel($this->namespace, (string)$process->id());
        $process->removeTouchCallback($this->touchCallback);
    }

    protected function getAll(): array
    {
        $processes = $this->redis->hGetAll($this->namespace);
        array_walk($processes, function($process) {
            $process->addTouchCallback($this->touchCallback);
        });
        return $processes;
    }


}