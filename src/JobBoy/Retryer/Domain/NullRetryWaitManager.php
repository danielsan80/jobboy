<?php
declare(strict_types=1);

namespace JobBoy\Retryer\Domain;

class NullRetryWaitManager implements RetryWaitManager
{

    public function wait(int $currentAttempt): void
    {
    }
}
