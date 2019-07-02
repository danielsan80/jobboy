<?php

namespace JobBoy\Process\Domain\Entity\Data;

use JobBoy\Process\Domain\Entity\Id\ProcessId;
use JobBoy\Process\Domain\ProcessParameters;
use JobBoy\Process\Domain\ProcessStatus;

class ProcessData
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
    protected $waitingUntil;

    /** @var \DateTimeImmutable */
    protected $handledAt;

    /** @var array */
    protected $data;

    public function __construct(array $data = [])
    {
        foreach ($data as $key => $value) {
            $setter = 'set' . ucfirst($key);
            $this->$setter($value);
        }
    }

    public function setId(?ProcessId $id): self
    {
        $this->id = $id;
        return $this;
    }

    public function setCode(?string $code): self
    {
        $this->code = $code;
        return $this;
    }

    public function setParameters(?ProcessParameters $parameters): self
    {
        $this->parameters = $parameters;
        return $this;
    }

    public function setStatus(?ProcessStatus $status): self
    {
        $this->status = $status;
        return $this;
    }

    public function setCreatedAt(?\DateTimeImmutable $createdAt): self
    {
        $this->createdAt = $createdAt;
        return $this;
    }

    public function setUpdatedAt(?\DateTimeImmutable $updatedAt): self
    {
        $this->updatedAt = $updatedAt;
        return $this;
    }

    public function setStartedAt(?\DateTimeImmutable $startedAt): self
    {
        $this->startedAt = $startedAt;
        return $this;
    }

    public function setEndedAt(?\DateTimeImmutable $endedAt): self
    {
        $this->endedAt = $endedAt;
        return $this;
    }

    public function setWaitingUntil(?\DateTimeImmutable $waitingUntil): self
    {
        $this->waitingUntil = $waitingUntil;
        return $this;
    }

    public function setHandledAt(?\DateTimeImmutable $handledAt): self
    {
        $this->handledAt = $handledAt;
        return $this;
    }

    public function setData(?array $data): self
    {
        $this->data = $data;
        return $this;
    }

    public function id(): ?ProcessId
    {
        return $this->id;
    }

    public function code(): ?string
    {
        return $this->code;
    }

    public function parameters(): ?ProcessParameters
    {
        return $this->parameters;
    }

    public function status(): ?ProcessStatus
    {
        return $this->status;
    }

    public function createdAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function updatedAt(): ?\DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function startedAt(): ?\DateTimeImmutable
    {
        return $this->startedAt;
    }

    public function endedAt(): ?\DateTimeImmutable
    {
        return $this->endedAt;
    }

    public function waitingUntil(): ?\DateTimeImmutable
    {
        return $this->waitingUntil;
    }

    public function handledAt(): ?\DateTimeImmutable
    {
        return $this->handledAt;
    }

    public function data(): ?array
    {
        return $this->data;
    }

}
