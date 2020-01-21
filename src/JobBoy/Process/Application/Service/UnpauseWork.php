<?php

namespace JobBoy\Process\Application\Service;

use JobBoy\Process\Domain\PauseControl\PauseControl;

class UnpauseWork
{

    /** @var PauseControl */
    protected $pauseControl;

    public function __construct(
        PauseControl $pauseControl
    )
    {
        $this->pauseControl = $pauseControl;
    }

    public function execute(): void
    {
        $this->pauseControl->unpause();
    }

}