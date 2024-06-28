<?php
declare(strict_types=1);

namespace JobBoy\Retryer\Domain;

class DefaultRetryerFactory
{
    public function create(int $maxAttempts = 1, int $msecsWait = 100): DefaultRetryer
    {
        return new DefaultRetryer(
            new FibonacciRetryWaitManager($msecsWait),
            $maxAttempts
        );
    }

}
