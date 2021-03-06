<?php

namespace JobBoy\Process\Domain\Entity\Infrastructure\Normalizable;

use Assert\Assertion;
use JobBoy\Clock\Domain\Clock;
use JobBoy\Process\Domain\Entity\Id\ProcessId;
use JobBoy\Process\Domain\Entity\Infrastructure\Normalizable\Data\NormalizableProcessData;
use JobBoy\Process\Domain\Entity\Process;
use JobBoy\Process\Domain\NormalizableInterface;
use JobBoy\Process\Domain\ProcessParameters;
use JobBoy\Process\Domain\ProcessStatus;
use JobBoy\Process\Domain\ProcessStore;

class NormalizableProcess extends Process implements NormalizableInterface
{

    protected function __construct(NormalizableProcessData $data)
    {
        parent::__construct($data);

        $this->check($data);

        $this->setStatus($data->status());

        $now = Clock::createDateTimeImmutable();
        $this->setCreatedAt($data->createdAt(), $now);
        $this->setUpdatedAt($data->updatedAt(), $now);
        $this->setStartedAt($data->startedAt());
        $this->setEndedAt($data->endedAt());
        $this->setHandledAt($data->handledAt());
        $this->setKilledAt($data->killedAt());

        $this->setStore($data->store());
        $this->setReports($data->reports());

    }

    protected function check(NormalizableProcessData $data): void
    {
        if (
            $data->status()===null
        ) {
            Assertion::null($data->createdAt());
            Assertion::null($data->updatedAt());
            Assertion::null($data->startedAt());
            Assertion::null($data->endedAt());
            Assertion::null($data->handledAt());
            Assertion::null($data->store());
            Assertion::null($data->reports());
            return;
        }

        Assertion::notNull($data->createdAt());
        Assertion::notNull($data->updatedAt());
        Assertion::notNull($data->store());
        Assertion::notNull($data->reports());

        if ($data->status()->isStarting()) {
            Assertion::null($data->startedAt());
        }

        if (!$data->status()->isStarting()) {
            Assertion::notNull($data->startedAt());
        }

        if ($data->status()->isActive()) {
            Assertion::null($data->endedAt());
        }

        if (!$data->status()->isActive() && $data->startedAt()) {
            Assertion::notNull($data->endedAt());
        }

        if (!$data->status()->isActive() && !$data->startedAt()) {
            Assertion::null($data->endedAt());
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

    protected function setHandledAt(?\DateTimeImmutable $handledAt): void
    {
        $this->handledAt = $handledAt;
    }

    protected function setKilledAt(?\DateTimeImmutable $killedAt): void
    {
        $this->killedAt = $killedAt;
    }

    protected function setStore(?ProcessStore $store): void
    {
        if (!$store) {
            $store = new ProcessStore();
        }
        $this->store = $store;
    }

    protected function setReports(?ProcessStore $reports): void
    {
        if (!$reports) {
            $reports = new ProcessStore();
        }
        $this->reports = $reports;
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
            'handled_at' => self::normalizeDateTime($this->handledAt),
            'killed_at' => self::normalizeDateTime($this->killedAt),
            'store' => $this->store->data(),
            'reports' => $this->reports->data(),
        ];
    }

    public static function denormalize(array $data)
    {
        $data = array_replace([
            'id' => null,
            'code' => null,
            'parameters' => null,
            'status' => null,
            'created_at' => null,
            'updated_at' => null,
            'started_at' => null,
            'ended_at' => null,
            'handled_at' => null,
            'killed_at' => null,
            'store' => null,
            'reports' => null,
        ], $data);

        $processData = new NormalizableProcessData();
        $processData
            ->setId(new ProcessId($data['id']))
            ->setCode($data['code'])
            ->setParameters(new ProcessParameters($data['parameters']))
            ->setStatus(new ProcessStatus($data['status']))
            ->setCreatedAt(self::denormalizeDateTime($data['created_at']))
            ->setUpdatedAt(self::denormalizeDateTime($data['updated_at']))
            ->setStartedAt(self::denormalizeDateTime($data['started_at']))
            ->setEndedAt(self::denormalizeDateTime($data['ended_at']))
            ->setHandledAt(self::denormalizeDateTime($data['handled_at']))
            ->setKilledAt(self::denormalizeDateTime($data['killed_at']))
            ->setStore(new ProcessStore($data['store']))
            ->setReports(new ProcessStore($data['reports']))
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
