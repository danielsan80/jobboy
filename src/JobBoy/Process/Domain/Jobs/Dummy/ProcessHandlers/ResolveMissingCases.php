<?php

namespace JobBoy\Process\Domain\Jobs\Dummy\ProcessHandlers;

use JobBoy\Process\Domain\Entity\Id\ProcessId;
use JobBoy\Process\Domain\Jobs\Common\ProcessHandlers\ResolveMissingCases as BaseResolveMissingCases;
use JobBoy\Process\Domain\Jobs\Dummy\Job;

class ResolveMissingCases extends BaseResolveMissingCases
{

    public function supports(ProcessId $id): bool
    {
        return $this->process($id)->code() === Job::CODE;
    }

}