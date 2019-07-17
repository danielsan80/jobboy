<?php

namespace JobBoy\Process\Domain\ProcessIterator;

class IterationResponse
{

    /** @var bool */
    protected $hasWorked;

    public function __construct(bool $hasWorked = true)
    {
        $this->hasWorked = $hasWorked;
    }


    public function hasWorked(): bool
    {
        return $this->hasWorked;
    }

}