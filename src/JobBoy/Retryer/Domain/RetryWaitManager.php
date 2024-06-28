<?php
declare(strict_types=1);

namespace JobBoy\Retryer\Domain;

interface RetryWaitManager
{
    public function wait(int $currentAttempt): void;
}
