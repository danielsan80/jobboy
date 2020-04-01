<?php

namespace JobBoy\Process\Domain\Jobs\Dummy;

use Assert\Assertion;
use JobBoy\Process\Domain\Entity\Id\ProcessId;
use JobBoy\Process\Domain\ProcessHandler\IterationResponse;
use JobBoy\Process\Domain\ProcessHandler\ProcessHandlers\Base\AbstractUnhandledProcessHandler;

class Initialize extends AbstractUnhandledProcessHandler
{

    protected function doSupports(ProcessId $id): bool
    {
        return $this->process($id)->code() === Job::CODE && $this->process($id)->status()->isStarting();
    }

    public function handle(ProcessId $id): IterationResponse
    {
        $iterationDuration = $this->process($id)->parameters()->get('iteration.duration', Job::DEFAULT_ITERATION_DURATION);
        $processDuration = $this->process($id)->parameters()->get('process.duration', Job::DEFAULT_PROCESS_DURATION);

        Assertion::integer($iterationDuration, 'The "iteration.duration" parameter is in seconds and must be an integer');
        Assertion::integer($processDuration, 'The "process.duration" parameter is in seconds and must be an integer');

        Assertion::range($iterationDuration, 0, 60,
            "An iteration should not last more then 10-30 seconds, so the dummy iteration is limited to 60 seconds"
        );
        Assertion::range($processDuration, 0, 60 * 60,
            "A process could run for days but a dummy process is limited to 1 hour"
        );


        $this->process($id)->set('last_until', $this->process($id)->createdAt()->getTimestamp() + $processDuration);

        $this->process($id)->set('iteration.duration', $iterationDuration);
        $this->process($id)->set('process.duration', $processDuration);

        $this->process($id)->changeStatusToRunning();

        return new IterationResponse();
    }
}