<?php

namespace JobBoy\Process\Domain\Jobs\Common\ProcessHandlers;

use JobBoy\Process\Domain\Entity\Id\ProcessId;
use JobBoy\Process\Domain\ProcessHandler\IterationResponse;
use JobBoy\Process\Domain\ProcessHandler\ProcessHandlers\Base\AbstractUnhandledProcessHandler;

/**
 * It is a generic Dummy ProcessHandler who support all processes and move them to end to avoid errors.
 * It is used just to cover the missing cases until the right process handler was implemented
 */
class ResolveMissingCases extends AbstractUnhandledProcessHandler
{

    public function doSupports(ProcessId $id): bool
    {
        return true;
    }

    public function handle(ProcessId $id): IterationResponse
    {
        $process = $this->process($id);

        if ($process->status()->isFailing()) {
            $process->changeStatusToFailed();
            return new IterationResponse();
        }

        if ($process->status()->isStarting()) {
            $process->changeStatusToRunning();
            return new IterationResponse();
        }
        if ($process->status()->isRunning()) {
            $process->changeStatusToEnding();
            return new IterationResponse();
        }
        if ($process->status()->isEnding()) {
            $process->changeStatusToCompleted();
            return new IterationResponse();
        }

        throw new \LogicException('This code should not be executed');
    }

}