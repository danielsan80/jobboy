<?php

namespace JobBoy\Clock\Domain;

class Timer
{
    protected $startTime;
    protected $timeout;

    protected $realStartTime;

    public function __construct(int $timeout)
    {
        $this->startTime = Clock::microtime();
        $this->timeout = $timeout;
    }

    public function isTimedout(): bool
    {
        $now = Clock::microtime();

        return ($now - $this->startTime) > $this->timeout;
    }


}