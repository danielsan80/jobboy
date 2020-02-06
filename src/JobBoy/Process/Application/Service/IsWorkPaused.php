<?php

namespace JobBoy\Process\Application\Service;

use JobBoy\Process\Domain\PauseControl\PauseControl;

class IsWorkPaused
{

    /** @var PauseControl */
    protected $pauseControl;

    public function __construct(
        PauseControl $pauseControl
    )
    {
        $this->pauseControl = $pauseControl;
    }

    public function execute(): bool
    {
        return $this->pauseControl->isPaused();
    }

}