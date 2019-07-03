<?php

namespace JobBoy\Process\Domain\Entity;

use Assert\Assertion;
use Dan\Clock\Domain\Clock;
use JobBoy\Process\Domain\Entity\Data\CreateProcessData;
use JobBoy\Process\Domain\Entity\Data\ProcessData;
use JobBoy\Process\Domain\Entity\Id\ProcessId;
use JobBoy\Process\Domain\NormalizableInterface;
use JobBoy\Process\Domain\ProcessParameters;
use JobBoy\Process\Domain\ProcessStatus;
use JobBoy\Process\Domain\ProcessStore;

class NormalizableProcess extends Process implements NormalizableInterface
{

    static public function create(CreateProcessData $data): self
    {

        $data = new ProcessData([
            'id' => $data->id(),
            'code' => $data->code(),
            'parameters' => $data->parameters()
        ]);
        return new static($data);

    }

    protected function __construct( ProcessData $data)
    {

        $this->check($data);

        $this->setId($data->id());
        $this->setCode($data->code());
        $this->setParameters($data->parameters());


        $this->setStatus($data->status());

        $now = Clock::createDateTimeImmutable();
        $this->setCreatedAt($data->createdAt(), $now);
        $this->setUpdatedAt($data->updatedAt(), $now);
        $this->setStartedAt($data->startedAt());
        $this->setEndedAt($data->endedAt());
        $this->setWaitingUntil($data->waitingUntil());
        $this->setHandledAt($data->handledAt(), $now);

        $this->setStore($data->store());

    }

    protected function check(ProcessData $data): void
    {
        if (
            $data->status()===null
        ) {
            Assertion::null($data->createdAt());
            Assertion::null($data->updatedAt());
            Assertion::null($data->startedAt());
            Assertion::null($data->endedAt());
            Assertion::null($data->waitingUntil());
            Assertion::null($data->handledAt());
            Assertion::null($data->store());
            return;
        }

        Assertion::notNull($data->createdAt());
        Assertion::notNull($data->updatedAt());
        Assertion::notNull($data->store());

        if ($data->status()->isStarting()) {
            Assertion::null($data->startedAt());
        }

        if (!$data->status()->isStarting()) {
            Assertion::notNull($data->startedAt());
        }

        if ($data->status()->isWaiting()) {
            Assertion::notNull($data->waitingUntil());
        }

        if (!$data->status()->isWaiting()) {
            Assertion::null($data->waitingUntil());
        }

        if ($data->status()->isActive()) {
            Assertion::null($data->endedAt());
        }

        if (!$data->status()->isActive()) {
            Assertion::notNull($data->endedAt());
        }

    }


    protected function setStatus(?ProcessStatus $status): void {
        if (!$status) {
            $status = ProcessStatus::starting();
        }
        $this->status = $status;
    }

    protected function setCreatedAt(?\DateTimeImmutable $createdAt, ?\DateTimeImmutable $default = null): void
    {
        if (!$createdAt) {
            $createdAt = $default;
        }
        $this->createdAt = $createdAt;
    }


    protected function setUpdatedAt(?\DateTimeImmutable $updatedAt, ?\DateTimeImmutable $default = null): void
    {
        if (!$updatedAt) {
            $updatedAt = $default;
        }
        $this->updatedAt = $updatedAt;
    }

    protected function setStartedAt(?\DateTimeImmutable $startedAt): void
    {
        $this->startedAt = $startedAt;
    }

    protected function setEndedAt(?\DateTimeImmutable $endedAt): void
    {
        $this->endedAt = $endedAt;
    }

    protected function setWaitingUntil(?\DateTimeImmutable $waitingUntil): void
    {
        $this->waitingUntil = $waitingUntil;
    }

    protected function setHandledAt(?\DateTimeImmutable $handledAt, ?\DateTimeImmutable $default = null): void
    {
        if (!$handledAt) {
            $handledAt = $default;
        }

        $this->handledAt = $handledAt;
    }

    protected function setStore(?ProcessStore $store): void
    {
        if (!$store) {
            $store = new ProcessStore();
        }
        $this->store = $store;
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

    public function normalize(): array
    {
        return [
            'id' => $this->id->toScalar(),
            'code' => $this->code,
            'parameters' => $this->parameters->toScalar(),
            'status' => $this->status->toScalar(),
            'created_at' => self::normalizeDateTime($this->createdAt),
            'updated_at' => self::normalizeDateTime($this->updatedAt),
            'started_at' => self::normalizeDateTime($this->startedAt),
            'ended_at' => self::normalizeDateTime($this->endedAt),
            'waiting_until' => self::normalizeDateTime($this->waitingUntil),
            'handled_at' => self::normalizeDateTime($this->handledAt),
            'store' => $this->store->data(),
        ];
    }

    public static function denormalize(array $data): self
    {
        $processData = new ProcessData();
        $processData
            ->setId(new ProcessId($data['id']))
            ->setCode($data['code'])
            ->setParameters(new ProcessParameters($data['parameters']))
            ->setStatus(new ProcessStatus($data['status']))
            ->setCreatedAt(self::denormalizeDateTime($data['created_at']))
            ->setUpdatedAt(self::denormalizeDateTime($data['updated_at']))
            ->setStartedAt(self::denormalizeDateTime($data['started_at']))
            ->setEndedAt(self::denormalizeDateTime($data['ended_at']))
            ->setWaitingUntil(self::denormalizeDateTime($data['waiting_until']))
            ->setHandledAt(self::denormalizeDateTime($data['handled_at']))
            ->setStore(new ProcessStore($data['store']))
        ;

        return new static($processData);

    }

    protected static function normalizeDateTime(?\DateTimeImmutable $value): ?string
    {
        return $value?$value->format(\DateTime::ISO8601):null;
    }

    protected static function denormalizeDateTime($value): ?\DateTimeImmutable
    {
        return $value?\DateTimeImmutable::createFromFormat(\DateTime::ISO8601, $value):null;
    }
}
