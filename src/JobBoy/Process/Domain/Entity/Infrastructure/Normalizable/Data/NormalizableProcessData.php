<?php

namespace JobBoy\Process\Domain\Entity\Infrastructure\Normalizable\Data;

use JobBoy\Process\Domain\Entity\Data\ProcessData;
use JobBoy\Process\Domain\ProcessStatus;
use JobBoy\Process\Domain\ProcessStore;

class NormalizableProcessData extends ProcessData
{

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

    public function setHandledAt(?\DateTimeImmutable $handledAt): self
    {
        $this->handledAt = $handledAt;
        return $this;
    }


    public function setKilledAt(?\DateTimeImmutable $killedAt): self
    {
        $this->killedAt = $killedAt;
        return $this;
    }

    public function setStore(?ProcessStore $store): self
    {
        $this->store = $store;
        return $this;
    }

    public function setReports(?ProcessStore $reports): self
    {
        $this->reports = $reports;
        return $this;
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

    public function handledAt(): ?\DateTimeImmutable
    {
        return $this->handledAt;
    }

    public function killedAt(): ?\DateTimeImmutable
    {
        return $this->killedAt;
    }

    public function store(): ?ProcessStore
    {
        return $this->store;
    }

    public function reports(): ?ProcessStore
    {
        return $this->reports;
    }

}
