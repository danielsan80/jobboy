<?php

namespace JobBoy\Process\Application\Service;

use JobBoy\Process\Domain\Entity\Id\ProcessId;

class IterateProcess
{

    /** @var MainProcessHandler */
    protected $mainProcessHandler;

    public function __construct(
        MainProcessHandler $mainProcessHandler
    )
    {
        $this->mainProcessHandler = $mainProcessHandler;
    }

    public function execute(ProcessId $id): void
    {
        $this->mainProcessHandler->handle($id);
    }

}