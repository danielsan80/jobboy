<?php

namespace JobBoy\Process\Domain;

/**
 * @immutable
 */
class ProcessStatus
{
    const STARTING = 'starting';
    const RUNNING = 'running';
    const WAITING = 'waiting';
    const ENDING = 'ending';
    const FAILED = 'failed';
    const COMPLETED = 'completed';

    /** @var string */
    protected $value;

    public function __construct(string $value)
    {
        if (!in_array($value, self::allStates())) {
            throw new \InvalidArgumentException('Invalid process status value "'.$value.'"');
        }

        $this->value = $value;
    }

    public static function allStates(): array
    {
        return [
            self::STARTING,
            self::RUNNING,
            self::WAITING,
            self::ENDING,
            self::FAILED,
            self::COMPLETED,
        ];
    }

    public static function starting(): self
    {
        return new static(self::STARTING);
    }

    public static function running(): self
    {
        return new static(self::RUNNING);
    }

    public static function waiting(): self
    {
        return new static(self::WAITING);
    }

    public static function ending(): self
    {
        return new static(self::ENDING);
    }

    public static function failed(): self
    {
        return new static(self::FAILED);
    }

    public static function completed(): self
    {
        return new static(self::COMPLETED);
    }

    public function isStarting(): bool
    {
        return $this->value === self::STARTING;
    }

    public function isRunning(): bool
    {
        return $this->value === self::RUNNING;
    }

    public function isWaiting(): bool
    {
        return $this->value === self::WAITING;
    }

    public function isEnding(): bool
    {
        return $this->value === self::ENDING;
    }

    public function isFailed(): bool
    {
        return $this->value === self::FAILED;
    }

    public function isCompleted(): bool
    {
        return $this->value === self::COMPLETED;
    }

    /**
     * Is Active when there is something to do again
     * @return bool
     */
    public function isActive(): bool
    {
        return in_array($this->value, self::activeStates());
    }

    public static function activeStates(): array
    {
        return [
            self::STARTING,
            self::RUNNING,
            self::WAITING,
            self::ENDING,
        ];
    }

    /**
     * Is Evolving when is Active and the process is started
     * @return bool
     */
    public function isEvolving(): bool
    {
        return in_array($this->value, self::evolvingStates());
    }

    public static function evolvingStates(): array
    {
        return [
            self::RUNNING,
            self::WAITING,
            self::ENDING,
        ];
    }

    public static function fromScalar(string $value): self
    {
        return new static($value);
    }

    public function value(): string
    {
        return $this->value;
    }

    public function toScalar(): string
    {
        return $this->value();
    }

    public function equals(ProcessStatus $processStatus): bool
    {
        return $this->value() === $processStatus->value();
    }

    public function change(ProcessStatus $processStatus): self
    {
        if ($this->equals($processStatus)) {
            return $processStatus;
        }

        if (in_array($processStatus->value(), [
            self::STARTING,
        ])) {
            throw new \InvalidArgumentException(sprintf('Status transition from "%s" to "%s" is not allowed', $this->value(), $processStatus->value()));
        }

        if (in_array($this->value(), [
            self::FAILED,
            self::COMPLETED,
        ])) {
            throw new \InvalidArgumentException(sprintf('Status transition from "%s" to "%s" is not allowed', $this->value(), $processStatus->value()));
        }

        if (
            in_array($this->value(), [self::ENDING,])
            && !in_array($processStatus->value(), [self::FAILED, self::COMPLETED])  ) {
            throw new \InvalidArgumentException(sprintf('Status transition from "%s" to "%s" is not allowed', $this->value(), $processStatus->value()));
        }

        return new static($processStatus->value());
    }

    public function __toString()
    {
        return $this->toScalar();
    }

}