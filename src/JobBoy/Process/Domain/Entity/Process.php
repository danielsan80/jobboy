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

    /** @var \DateTimeImmutable */
    protected $killedAt;

    /** @var ProcessStore */
    protected $store;

    /** @var ProcessStore */
    protected $reports;

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
        $this->reports = new ProcessStore();
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

    /**
     * @todo remove the fix
     */
    public function reports(): ProcessStore
    {
        if (!$this->reports) {
            $this->reports = new ProcessStore();
        }
        return $this->reports;
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

        if ($this->startedAt) {
            $this->endedAt = Clock::createDateTimeImmutable();
        }
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

    /**
     * @return \DateTimeImmutable
     */
    public function killedAt(): ?\DateTimeImmutable
    {
        return $this->killedAt;
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

    public function add($key, $value): void
    {
        $currentValues = $this->store->get($key, []);
        Assertion::isArray($currentValues);
        $currentValues[] = $value;
        $store = $this->store->set($key, $currentValues);
        if ($this->store->equals($store)) {
            return;
        }
        $this->store = $store;
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


    public function setReport($key, $value): void
    {
        $reports = $this->reports->set($key, $value);
        if ($this->reports->equals($reports)) {
            return;
        }
        $this->reports = $reports;
        $this->touch();
    }


    public function unsetReport($key): void
    {
        $reports = $this->reports->unset($key);
        if ($this->reports->equals($reports)) {
            return;
        }
        $this->reports = $reports;
        $this->touch();
    }

    public function hasReport($key): bool
    {
        return $this->reports->has($key);
    }

    public function getReport($key, $default = null)
    {
        return $this->reports->get($key, $default);
    }

    public function incReport($key, $step = 1): void
    {
        $value = $this->reports->get($key, 0) + $step;
        $this->reports = $this->reports->set($key, $value);
        $this->touch();
    }

    public function decReport($key, $step = 1): void
    {
        $value = $this->reports->get($key, 0) - $step;
        $this->reports = $this->reports->set($key, $value);
        $this->touch();
    }

    public function addReport($key, $value): void
    {
        $currentValues = $this->reports->get($key, []);
        Assertion::isArray($currentValues);
        $currentValues[] = $value;
        $reports = $this->reports->set($key, $currentValues);
        if ($this->reports->equals($reports)) {
            return;
        }
        $this->reports = $reports;
        $this->touch();
    }

    public function prependReports($data): void
    {
        $reports = $this->reports->prepend($data);
        if ($this->reports->equals($reports)) {
            return;
        }

        $this->reports = $reports;
        $this->touch();
    }

    public function appendReports($data): void
    {
        $reports = $this->reports->append($data);
        if ($this->reports->equals($reports)) {
            return;
        }

        $this->reports = $reports;
        $this->touch();
    }

    public function kill(): void
    {
        if (
            $this->status->isCompleted() ||
            $this->status->isFailed() ||
            $this->status->isFailing()
        ) {
            return;
        }

        $this->killedAt = Clock::createDateTimeImmutable();

        if ($this->status()->isStarting()) {
            $this->changeStatusToFailed();
            return;
        }

        $this->changeStatusToFailing();
    }

}
