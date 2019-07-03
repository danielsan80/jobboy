<?php

namespace JobBoy\Process\Domain\ProcessHandler;

use JobBoy\Process\Domain\Entity\Id\ProcessId;

class MainProcessHandler
{

    /** @var ProcessHandlerRegistry  */
    protected $registry;

    public function __construct(ProcessHandlerRegistry $registry)
    {
        $this->registry = $registry;
    }

    public function handle(ProcessId $id): void
    {
        $handler = $this->registry->get($id);
        $handler->handle($id);
    }

}