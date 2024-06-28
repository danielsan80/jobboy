<?php
declare(strict_types=1);

namespace JobBoy\Retryer\Domain;

use Assert\Assertion;

class FibonacciRetryWaitManager implements RetryWaitManager
{
    private $waitMsecs;

    public function __construct($waitMsecs = 0)
    {
        $this->waitMsecs = $waitMsecs;
    }

    public function wait(int $currentAttempt): void
    {
        Assertion::greaterThan($currentAttempt, 0);
        $this->usleep($this->msecsToWait($currentAttempt));
    }

    private function msecsToWait($attempt): int
    {
        if ($attempt == 1) {
            return $this->waitMsecs;
        }
        if ($attempt == 2) {
            return $this->waitMsecs;
        }
        return $this->msecsToWait($attempt - 2) + $this->msecsToWait($attempt - 1);
    }

    protected function usleep(int $msecs): void
    {
        usleep($msecs * 1000);
    }
}
