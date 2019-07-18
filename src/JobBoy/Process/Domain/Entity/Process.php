<?php

namespace JobBoy\Process\Domain\Entity;

use Assert\Assertion;
use JobBoy\Clock\Domain\Clock;
use JobBoy\Process\Domain\Entity\Data\ProcessData;
use JobBoy\Process\Domain\Entity\Id\ProcessId;
use JobBoy\Process\Domain\ProcessParameters;
use JobBoy\Process\Domain\ProcessStatus;
use JobBoy\Process\Domain\ProcessStore;

class Process
{

    /** @var ProcessId */
    protected $id;

    /** @var string */
    protected $code;

    /** @var ProcessParameters */
    protected $parameters;

    /** @var ProcessStatus */
    protected $status;

    /** @var \DateTimeImmutable */
    protected $createdAt;

    /** @var \DateTimeImmutable */
    protected $updatedAt;

    /** @var \DateTimeImmutable */
    protected $startedAt;

    /** @var \DateTimeImmutable */
    protected $endedAt;

    /** @var \DateTimeImmutable */
    protected $handledAt;

    /** @var ProcessStore */
    protected $store;

    static public function create(ProcessData $data): self
    {
        return new static($data);
    }

    protected function __construct(ProcessData $data)
    {

        $this->setId($data->id());
        $this->setCode($data->code());
        $this->setParameters($data->parameters());

        $this->status = ProcessStatus::starting();

        $now = Clock::createDateTimeImmutable();
        $this->createdAt = $now;
        $this->updatedAt = $now;

        $this->store = new ProcessStore();
    }

    protected function setId(ProcessId $id): void
    {
        $this->id = $id;
    }

    protected function setCode(string $code): void
    {
        Assertion::notEmpty($code);
        $this->code = $code;
    }

    protected function setParameters(?ProcessParameters $parameters): void
    {
        if (!$parameters) {
            $parameters = new ProcessParameters();
        }

        $this->parameters = $parameters;
    }

    public function id(): ProcessId
    {
        return $this->id;
    }

    public function code(): string
    {
        return $this->code;
    }

    public function status(): ProcessStatus
    {
        return $this->status;
    }

    public function parameters(): ProcessParameters
    {
        return $this->parameters;
    }

    public function store(): ProcessStore
    {
        return $this->store;
    }

    protected function changeStatus(ProcessStatus $status): void
    {
        if (!$this->status->equals($status)) {
            $this->status = $this->status->change($status);
            $this->touch();
        }
    }

    public function changeStatusToRunning(): void
    {
        if ($this->status->isStarting()) {
            $this->startedAt = Clock::createDateTimeImmutable();
        }
        $this->changeStatus(ProcessStatus::running());
    }

    public function changeStatusToFailing(): void
    {
        $this->changeStatus(ProcessStatus::failing());
    }

    public function changeStatusToFailed(): void
    {
        $this->endedAt = Clock::createDateTimeImmutable();
        $this->changeStatus(ProcessStatus::failed());
    }

    public function changeStatusToEnding(): void
    {
        $this->changeStatus(ProcessStatus::ending());
    }

    public function changeStatusToCompleted(): void
    {
        $this->endedAt = Clock::createDateTimeImmutable();
        $this->changeStatus(ProcessStatus::completed());
    }


    /**
     * @return \DateTimeImmutable
     */
    public function createdAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    /**
     * @return \DateTimeImmutable
     */
    public function updatedAt(): \DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function touch(): void
    {
        $this->updatedAt = Clock::createDateTimeImmutable();
    }

    /**
     * @return \DateTimeImmutable
     */
    public function startedAt(): ?\DateTimeImmutable
    {
        return $this->startedAt;
    }

    /**
     * @return \DateTimeImmutable
     */
    public function endedAt(): ?\DateTimeImmutable
    {
        return $this->endedAt;
    }

    /**
     * @return \DateTimeImmutable
     */
    public function handledAt(): ?\DateTimeImmutable
    {
        return $this->handledAt;
    }

    public function isHandled(): bool
    {
        return (bool)$this->handledAt;
    }

    public function handle(): void
    {
        $this->handledAt = Clock::createDateTimeImmutable();
        $this->touch();
    }

    public function release(): void
    {
        if ($this->handledAt === null) {
            return;
        }
        $this->handledAt = null;
        $this->touch();
    }

    public function set($key, $value): void
    {
        $store = $this->store->set($key, $value);
        if ($this->store->equals($store)) {
            return;
        }
        $this->store = $store;
        $this->touch();
    }

    public function unset($key): void
    {
        $store = $this->store->unset($key);
        if ($this->store->equals($store)) {
            return;
        }
        $this->store = $store;
        $this->touch();
    }

    public function has($key): bool
    {
        return $this->store->has($key);
    }

    public function get($key, $default = null)
    {
        return $this->store->get($key, $default);
    }

    public function inc($key, $step = 1): void
    {
        $value = $this->store->get($key, 0) + $step;
        $this->store = $this->store->set($key, $value);
        $this->touch();
    }

    public function dec($key, $step = 1): void
    {
        $value = $this->store->get($key, 0) - $step;
        $this->store = $this->store->set($key, $value);
        $this->touch();
    }

    public function prepend($data): void
    {
        $store = $this->store->prepend($data);
        if ($this->store->equals($store)) {
            return;
        }
        $this->store = $store;
        $this->touch();
    }

    public function append($data): void
    {
        $store = $this->store->append($data);
        if ($this->store->equals($store)) {
            return;
        }

        $this->store = $store;
        $this->touch();
    }

}
