<?php

namespace JobBoy\Process\Domain\Jobs\Dummy;

use JobBoy\Clock\Domain\Clock;
use JobBoy\Process\Domain\Entity\Id\ProcessId;
use JobBoy\Process\Domain\ProcessHandler\IterationResponse;
use JobBoy\Process\Domain\ProcessHandler\ProcessHandlers\Base\AbstractUnhandledProcessHandler;

class Iterate extends AbstractUnhandledProcessHandler
{

    protected function doSupports(ProcessId $id): bool
    {
        return $this->process($id)->code()===Job::CODE && $this->process($id)->status()->isRunning();
    }

    public function handle(ProcessId $id): IterationResponse
    {
        $iterationDuration = $this->process($id)->get('iteration.duration');

        $now = Clock::createDateTimeImmutable();

        if ($now->getTimestamp() > $this->process($id)->get('last_until')) {
            $this->process($id)->changeStatusToEnding();
            return new IterationResponse();
        }

        sleep($iterationDuration);

        return new IterationResponse();
    }
}