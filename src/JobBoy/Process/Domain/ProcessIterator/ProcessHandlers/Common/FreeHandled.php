<?php

namespace JobBoy\Process\Domain\ProcessIterator\ProcessHandlers\Common;

use JobBoy\Process\Domain\Entity\Id\ProcessId;
use JobBoy\Process\Domain\ProcessIterator\IterationResponse;
use JobBoy\Process\Domain\ProcessIterator\ProcessHandlers\Base\AbstractHandledProcessHandler;

/**
 * It is a generic ProcessHandler who support all handled processes and change the status to failing.
 */
class FreeHandled extends AbstractHandledProcessHandler
{

    protected function doSupports(ProcessId $id): bool
    {
        return true;
    }


    public function handle(ProcessId $id): IterationResponse
    {
        $this->process($id)->changeStatusToFailing();

        return new IterationResponse();
    }

}