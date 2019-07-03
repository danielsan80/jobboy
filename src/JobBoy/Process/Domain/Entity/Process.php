<?php

namespace JobBoy\Process\Domain\Entity;

use Assert\Assertion;
use Dan\Clock\Domain\Clock;
use JobBoy\Process\Domain\Entity\Data\ProcessData;
use JobBoy\Process\Domain\Entity\Id\ProcessId;
use JobBoy\Process\Domain\ProcessParameters;
use JobBoy\Process\Domain\ProcessStatus;
use JobBoy\Process\Domain\ProcessStore;

class Process
{

    const DEFAULT_WAIT_FOR = '1 minute';

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
    protected $waitingUntil;

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

    protected function changeStatus(ProcessStatus $processStatus): void
    {
        $this->status = $this->status->change($processStatus);
        $this->touch();
    }

    public function changeStatusToRunning(): void
    {
        if ($this->status->isStarting()) {
            $this->startedAt = Clock::createDateTimeImmutable();
        }
        $this->waitingUntil = null;
        $this->changeStatus(ProcessStatus::running());
    }

    public function changeStatusToWaiting(?string $waitFor = null): void
    {
        if (!$waitFor) {
            $waitFor = self::DEFAULT_WAIT_FOR;
        }

        $this->waitingUntil = Clock::createDateTimeImmutable('+ ' . $waitFor);

        $this->changeStatus(ProcessStatus::waiting());
    }

    public function changeStatusToCompleted(): void
    {
        $this->waitingUntil = null;
        $this->endedAt = Clock::createDateTimeImmutable();
        $this->changeStatus(ProcessStatus::completed());
    }

    public function changeStatusToFailed(): void
    {
        $this->waitingUntil = null;
        $this->endedAt = Clock::createDateTimeImmutable();
        $this->changeStatus(ProcessStatus::failed());
    }

    public function changeStatusToEnding(): void
    {
        $this->waitingUntil = null;
        $this->changeStatus(ProcessStatus::ending());
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
    public function waitingUntil(): ?\DateTimeImmutable
    {
        return $this->waitingUntil;
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
        $this->handledAt = null;
        $this->touch();
    }

    public function set($key, $value): void
    {
        $this->store = $this->store->set($key, $value);
        $this->touch();
    }

    public function unset($key): void
    {
        $this->store = $this->store->unset($key);
        $this->touch();
    }

    public function has($key): bool
    {
        return $this->store->has($key);
    }

    public function get($key, $default)
    {
        $this->store->get($key, $default);
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
        $this->store = $this->store->prepend($data);
        $this->touch();
    }

    public function append($data): void
    {
        $this->store = $this->store->append($data);
        $this->touch();
    }

}
