<?php

namespace JobBoy\Process\Application\DTO;

use JobBoy\Process\Domain\Entity\Process as ProcessEntity;

class Process
{

    /** @var string */
    protected $id;

    /** @var string */
    protected $code;

    /** @var array */
    protected $parameters;

    /** @var string */
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

    /** @var array */
    protected $store;


    public function __construct(ProcessEntity $process)
    {
        $this->id = $process->id()->toScalar();
        $this->code = $process->code();
        $this->parameters = $process->parameters()->toScalar();
        $this->status = $process->status()->toScalar();
        $this->createdAt = $process->createdAt();
        $this->updatedAt = $process->updatedAt();
        $this->startedAt = $process->startedAt();
        $this->endedAt = $process->endedAt();
        $this->handledAt = $process->handledAt();
        $this->store = $process->store()->data();

    }


    public function status(): string
    {
        return $this->status;
    }

    public function createdAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function updatedAt(): \DateTimeImmutable
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

    public function isStarted(): bool
    {
        return (bool)$this->startedAt;
    }

    public function isEnded(): bool
    {
        return (bool)$this->endedAt;
    }

    public function isDone(): bool
    {
        return $this->status=='completed';
    }

    public function isFailed(): bool
    {
        return $this->status=='failed';
    }


    public function isHandled(): bool
    {
        return (bool)$this->handledAt;
    }

    public function store(): array
    {
        return $this->store;
    }

}