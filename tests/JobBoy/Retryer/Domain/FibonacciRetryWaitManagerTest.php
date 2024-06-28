<?php
declare(strict_types=1);

namespace Tests\JobBoy\Retryer\Domain;

use JobBoy\Retryer\Domain\FibonacciRetryWaitManager;
use PHPUnit\Framework\TestCase;

class FibonacciRetryWaitManagerTest extends TestCase
{
    /**
     * @test
     * @dataProvider retryWaitManagerCases
     */
    public function it_works($currentAttempt, $expectedWaitedMsecs)
    {

        $retryWaitManager = new class(100) extends FibonacciRetryWaitManager {
            public $waitedMsecs = 0;

            protected function usleep(int $msecs): void
            {
                $this->waitedMsecs += $msecs;
            }
        };

        $retryWaitManager->wait($currentAttempt);

        $this->assertEquals($expectedWaitedMsecs, $retryWaitManager->waitedMsecs);

    }

    public function retryWaitManagerCases(): array
    {
        return [
            [1, 100],
            [2, 100],
            [3, 200],
            [4, 300],
            [5, 500],
            [6, 800],
            [7, 1300],
        ];
    }

    /**
     * @test
     * @dataProvider retryWaitManagerCases
     */
    public function currentAttempt_cannot_be_zero()
    {

        $retryWaitManager = new FibonacciRetryWaitManager(100);

        $this->expectExceptionMessage('Provided "0" is not greater than "0".');
        $retryWaitManager->wait(0);

    }
}
