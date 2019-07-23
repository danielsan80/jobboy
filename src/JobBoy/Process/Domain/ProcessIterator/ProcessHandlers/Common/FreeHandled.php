<?php

namespace JobBoy\Process\Domain\ProcessIterator\ProcessHandlers\Common;

use JobBoy\Process\Domain\Entity\Id\ProcessId;
use JobBoy\Process\Domain\ProcessIterator\IterationResponse;
use JobBoy\Process\Domain\ProcessIterator\ProcessHandlers\Base\AbstractHandledProcessHandler;

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