<?php

namespace JobBoy\Process\Domain\ProcessHandler;

use JobBoy\Process\Domain\Entity\Id\ProcessId;

interface ProcessHandlerInterface
{
    public function supports(ProcessId $id): bool;

    public function handle(ProcessId $id): IterationResponse;

}