<?php

namespace JobBoy\Job\Domain\Entity;

use JobBoy\Job\Domain\Entity\Id\JobExecutionId;

class JobExecution
{

    const DEFAULT_WAIT_FOR = '1 minute';

    /** @var JobExecutionId */
    protected $id;

    /** @var JobParameters */
    protected $jobParameters;

    /** @var SyncKey */
    protected $syncKey;

    /** @var JobStatus */
    protected $jobStatus;

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

    public function __construct(
        Id $id,
        JobInstance $jobInstance,
        JobParameters $jobParameters
    )
    {

        $this->setId($id);
        $this->setJobInstance($jobInstance);
        $this->setJobParameters($jobParameters);
        $this->initializeJobStatus();
        $this->initializeDateTimes();
        $this->setSyncKey();
    }

    protected function setId(Id $id): void
    {
        if (!$id) {
            throw new \InvalidArgumentException('The given id is empty');
        }
        $this->id = $id;
    }

    protected function setJobInstance(JobInstance $jobInstance): void
    {
        $this->jobInstance = $jobInstance;
        $jobInstance->addJobExecution($this, true);
        $this->job = $jobInstance->job();
    }


    protected function setJobParameters(?JobParameters $jobParameters): void
    {
        if (!$jobParameters) {
            $jobParameters = new JobParameters([]);
        }

        $defaultJobParameters = $this->jobInstance()->jobParameters();
        $jobParameters = $defaultJobParameters->merge($jobParameters);

        $this->jobParameters = $jobParameters;
    }

    protected function initializeJobStatus(): void
    {
        $this->jobStatus = JobStatus::starting();
    }

    protected function initializeDateTimes(): void
    {
        $this->createdAt = Clock::createDateTimeImmutable();
        $this->updatedAt = $this->createdAt;
        $this->handledAt = $this->createdAt;
    }

    protected function setSyncKey(): void
    {
        $syncKey = $this->jobInstance->syncKey();
        $this->syncKey = $syncKey->resolve($this);
    }

    public function reference(bool $internal = false): JobExecution
    {
        if (!$internal) {
            throw new \LogicException('This method is only for internal use');
        }
        return $this;
    }

    public function id(): Id
    {
        return $this->id;
    }

    public function jobInstance(): JobInstance
    {
        return $this->jobInstance;
    }

    public function job(): JobInterface
    {
        return $this->job;
    }

    public function syncKey(): SyncKey
    {
        return $this->syncKey;
    }

    public function jobStatus(): JobStatus
    {
        return $this->jobStatus;
    }

    public function jobParameters(): JobParameters
    {
        return $this->jobParameters;
    }

    protected function changeStatus(JobStatus $jobStatus): void
    {
        $this->jobStatus = $this->jobStatus->change($jobStatus);
        $this->touch();
    }

    public function changeStatusToRunning(): void
    {
        if ($this->jobStatus->isStarting()) {
            $this->startedAt = Clock::createDateTimeImmutable();
        }
        $this->waitingUntil = null;
        $this->changeStatus(JobStatus::running());
    }

    public function changeStatusToWaiting(?string $waitFor = null): void
    {
        if (!$waitFor) {
            $waitFor = self::DEFAULT_WAIT_FOR;
        }

        $this->waitingUntil = Clock::createDateTimeImmutable('+ ' . $waitFor);

        $this->changeStatus(JobStatus::waiting());
    }

    public function changeStatusToCompleted(): void
    {
        $this->waitingUntil = null;
        $this->endedAt = Clock::createDateTimeImmutable();
        $this->changeStatus(JobStatus::completed());
    }

    public function changeStatusToFailed(): void
    {
        $this->waitingUntil = null;
        $this->endedAt = Clock::createDateTimeImmutable();
        $this->changeStatus(JobStatus::failed());
    }

    public function changeStatusToEnding(): void
    {
        $this->waitingUntil = null;
        $this->changeStatus(JobStatus::ending());
    }

    public function detach(): void
    {
        if (!$this->jobInstance) {
            return;
        }

        $jobInstance = $this->jobInstance;
        $this->jobInstance = null;

        $jobInstance->removeJobExecution($this, true);
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
}