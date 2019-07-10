<?php

namespace JobBoy\Process\Domain;

/**
 * @immutable
 */
class ProcessStatus
{
    const STARTING = 'starting';
    const RUNNING = 'running';
    const FAILING = 'failing';
    const FAILED = 'failed';
    const ENDING = 'ending';
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
            self::FAILING,
            self::FAILED,
            self::ENDING,
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



    public static function failing(): self
    {
        return new static(self::FAILING);
    }

    public static function failed(): self
    {
        return new static(self::FAILED);
    }

    public static function ending(): self
    {
        return new static(self::ENDING);
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

    public function isFailing(): bool
    {
        return $this->value === self::FAILING;
    }

    public function isFailed(): bool
    {
        return $this->value === self::FAILED;
    }

    public function isEnding(): bool
    {
        return $this->value === self::ENDING;
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
            self::FAILING,
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
            self::FAILING,
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

    public function equals(ProcessStatus $status): bool
    {
        return $this->value() === $status->value();
    }

    public function change(ProcessStatus $status): self
    {
        if ($this->equals($status)) {
            return $status;
        }

        if (in_array($status->value(), [
            self::STARTING,
        ])) {
            throw new \InvalidArgumentException(sprintf('Status transition from "%s" to "%s" is not allowed', $this->value(), $status->value()));
        }

        if (in_array($this->value(), [
            self::FAILED,
            self::COMPLETED,
        ])) {
            throw new \InvalidArgumentException(sprintf('Status transition from "%s" to "%s" is not allowed', $this->value(), $status->value()));
        }

        if (
            in_array($this->value(), [self::ENDING,])
            && !in_array($status->value(), [self::FAILING, self::FAILED, self::COMPLETED])  ) {
            throw new \InvalidArgumentException(sprintf('Status transition from "%s" to "%s" is not allowed', $this->value(), $status->value()));
        }

        if (
            in_array($this->value(), [self::FAILING,])
            && !in_array($status->value(), [self::FAILED])  ) {
            throw new \InvalidArgumentException(sprintf('Status transition from "%s" to "%s" is not allowed', $this->value(), $status->value()));
        }

        return new static($status->value());
    }

    public function __toString()
    {
        return $this->toScalar();
    }

}