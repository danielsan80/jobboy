<?php

namespace JobBoy\Process\Domain\Jobs\Dummy\ProcessHandlers;

use JobBoy\Process\Domain\Entity\Id\ProcessId;
use JobBoy\Process\Domain\Jobs\Common\ProcessHandlers\FreeHandled as BaseFreeHandled;
use JobBoy\Process\Domain\Jobs\Dummy\Job;

class FreeHandled extends BaseFreeHandled
{
    protected function doSupports(ProcessId $id): bool
    {
        return $this->process($id)->code() === Job::CODE;
    }

}