<?php
declare(strict_types=1);

namespace JobBoy\Retryer\Domain;

class DefaultRetryer implements Retryer
{
    private $retryWaitManager;
    private $maxAttempts;

    public function __construct(RetryWaitManager $retryWaitManager, int $maxAttempts = 1)
    {
        $this->retryWaitManager = $retryWaitManager;
        $this->maxAttempts = $maxAttempts;
    }

    /**
     * @param callable(): mixed $f
     * @param callable(\Throwable $e, int $currentAttempt): bool|null $shouldRetry
     */
    public function try(callable $f, ?callable $shouldRetry = null)
    {
        if ($shouldRetry === null) {
            $shouldRetry = function (\Throwable $e, int $currentAttempt) {
                return true;
            };
        }

        $attempts = 0;

        while (true) {
            $attempts++;
            try {
                return $f($attempts);
            } catch (\Throwable $e) {
                if ($attempts >= $this->maxAttempts) {
                    throw $e;
                }
                if (!$shouldRetry($e, $attempts)) {
                    throw $e;
                }
                $this->retryWaitManager->wait($attempts);
            }
        }
    }

}
