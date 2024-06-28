<?php
declare(strict_types=1);

namespace Tests\JobBoy\Retryer\Domain;

use JobBoy\Retryer\Domain\DefaultRetryer;
use JobBoy\Retryer\Domain\NullRetryWaitManager;
use PHPUnit\Framework\TestCase;

class DefaultRetryerTest extends TestCase
{

    /**
     * @test
     */
    public function happy_path()
    {
        $retryer = new DefaultRetryer(new NullRetryWaitManager());

        $spy = new class() {
            public $ok = false;
        };

        $retryer->try(function (int $currentAttempt) use ($spy) {
            $spy->ok = true;
        }, function (\Throwable $e, int $currentAttempt) {
            return false;
        });

        $this->assertTrue($spy->ok);
    }

    /**
     * @test
     */
    public function it_has_a_max_attempts_limit()
    {
        $retryer = new DefaultRetryer(new NullRetryWaitManager());

        $this->expectExceptionMessage('I will never work');

        $retryer->try(function (int $currentAttempt) {
            throw new \RuntimeException('I will never work');
        }, function (\Throwable $e, int $currentAttempt) {
            return true;
        });
    }

    /**
     * @test
     */
    public function retry_5_times_then_throw_exception()
    {
        $retryer = new DefaultRetryer(new NullRetryWaitManager(), 5);

        $spy = new class() {
            public $attempts = 0;
        };

        try {
            $retryer->try(function (int $currentAttempt) use ($spy) {
                $spy->attempts++;
                throw new \RuntimeException('service temporarily down');
            }, function (\Throwable $e, int $currentAttempt) {
                if (!$e instanceof \RuntimeException) {
                    return false;
                }
                return true;
            });
            $this->fail('The retryer should throw an exception');
        } catch (\RuntimeException $e) {
            $this->assertEquals('service temporarily down', $e->getMessage());
        }

        $this->assertEquals(5, $spy->attempts);
    }

    /**
     * @test
     */
    public function retry_3_times_then_it_works()
    {
        $retryer = new DefaultRetryer(new NullRetryWaitManager(), 10);

        $spy = new class() {
            public $attempts = 0;
        };

        $retryer->try(function (int $currentAttempt) use ($spy) {
            $spy->attempts++;
            if ($spy->attempts < 3) {
                throw new \RuntimeException('service temporarily down');
            }
        }, function (\Throwable $e, int $currentAttempt) use ($spy) {
            if (!$e instanceof \RuntimeException) {
                return false;
            }
            return true;
        });

        $this->assertEquals(3, $spy->attempts);
    }


}
