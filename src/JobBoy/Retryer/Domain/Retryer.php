<?php
declare(strict_types=1);

namespace JobBoy\Retryer\Domain;

interface Retryer
{
    /**
     * @param callable(int $currentAttempt): void $f
     * @param ?callable(\Throwable $e, int $currentAttempt): bool $shouldRetry
     */
    public function try(callable $f, ?callable $shouldRetry = null);

}
