<?php

namespace Dan\Clock\Domain;

class Timer
{
    protected $startTime;
    protected $timeout;

    public function __construct(int $timeout)
    {
        $this->startTime = $this->microtime();
        $this->timeout = $timeout;
    }

    public function isTimedout(): bool
    {
        $now = $this->microtime();

        return ($now - $this->startTime) > $this->timeout;
    }

    protected function microtime(): float
    {
        return (float)Clock::createDateTimeImmutable()->format('U.u');
    }

}