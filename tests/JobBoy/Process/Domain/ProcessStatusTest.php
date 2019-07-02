<?php

namespace Tests\JobBoy\Process\Domain;

use PHPUnit\Framework\TestCase;

use JobBoy\Process\Domain\ProcessStatus;

class ProcessStatusTest extends TestCase
{

    /**
     * @test
     */
    public function throw_an_exception_for_invalid_value()
    {

        try {
            $processStatus = new ProcessStatus('an_invalid_value');
            $this->fail();

        } catch (\InvalidArgumentException $e) {
            $this->assertTrue(true);
        }

    }


    public function can_be_created_provider()
    {
        return [
            [ProcessStatus::STARTING, 'starting', 'isStarting'],
            [ProcessStatus::RUNNING, 'running', 'isRunning'],
            [ProcessStatus::WAITING, 'waiting', 'isWaiting'],
            [ProcessStatus::ENDING, 'ending', 'isEnding'],
            [ProcessStatus::FAILED, 'failed', 'isFailed'],
            [ProcessStatus::COMPLETED, 'completed', 'isCompleted'],
        ];

    }

    /**
     * @test
     * @dataProvider can_be_created_provider
     */
    public function can_be_created($value, $creator, $iser)
    {

        $processStatus = new ProcessStatus($value);
        $processStatus->equals(ProcessStatus::fromScalar($value));
        $this->assertEquals($value, $processStatus->toScalar());
        $this->assertEquals($value, $processStatus->value());
        $this->assertEquals($value, (string)$processStatus);
        $this->assertTrue($processStatus->equals(call_user_func(ProcessStatus::class .'::'.$creator)));
        $this->assertTrue($processStatus->$iser());

    }


    public function can_be_active_and_evolving_provider()
    {
        return [
            [ProcessStatus::STARTING, true, false],
            [ProcessStatus::RUNNING, true, true],
            [ProcessStatus::WAITING, true, true],
            [ProcessStatus::ENDING, true, true],
            [ProcessStatus::FAILED, false, false],
            [ProcessStatus::COMPLETED, false, false],
        ];

    }

    /**
     * @test
     * @dataProvider can_be_active_and_evolving_provider
     */
    public function can_be_active_and_evolving($value, $isActive, $isEvolving)
    {
        $processStatus = new ProcessStatus($value);
        $this->assertEquals($isActive, $processStatus->isActive());
        $this->assertEquals($isEvolving, $processStatus->isEvolving());
    }

    public function can_manage_transitions_provider()
    {
        return [
            [ProcessStatus::STARTING, ProcessStatus::STARTING, true],
            [ProcessStatus::STARTING, ProcessStatus::RUNNING, true],
            [ProcessStatus::STARTING, ProcessStatus::WAITING, true],
            [ProcessStatus::STARTING, ProcessStatus::ENDING, true],
            [ProcessStatus::STARTING, ProcessStatus::FAILED, true],
            [ProcessStatus::STARTING, ProcessStatus::COMPLETED, true],

            [ProcessStatus::RUNNING, ProcessStatus::STARTING, false],
            [ProcessStatus::RUNNING, ProcessStatus::RUNNING, true],
            [ProcessStatus::RUNNING, ProcessStatus::WAITING, true],
            [ProcessStatus::RUNNING, ProcessStatus::ENDING, true],
            [ProcessStatus::RUNNING, ProcessStatus::FAILED, true],
            [ProcessStatus::RUNNING, ProcessStatus::COMPLETED, true],

            [ProcessStatus::WAITING, ProcessStatus::STARTING, false],
            [ProcessStatus::WAITING, ProcessStatus::RUNNING, true],
            [ProcessStatus::WAITING, ProcessStatus::WAITING, true],
            [ProcessStatus::WAITING, ProcessStatus::ENDING, true],
            [ProcessStatus::WAITING, ProcessStatus::FAILED, true],
            [ProcessStatus::WAITING, ProcessStatus::COMPLETED, true],

            [ProcessStatus::ENDING, ProcessStatus::STARTING, false],
            [ProcessStatus::ENDING, ProcessStatus::RUNNING, false],
            [ProcessStatus::ENDING, ProcessStatus::WAITING, false],
            [ProcessStatus::ENDING, ProcessStatus::ENDING, true],
            [ProcessStatus::ENDING, ProcessStatus::FAILED, true],
            [ProcessStatus::ENDING, ProcessStatus::COMPLETED, true],

            [ProcessStatus::FAILED, ProcessStatus::STARTING, false],
            [ProcessStatus::FAILED, ProcessStatus::RUNNING, false],
            [ProcessStatus::FAILED, ProcessStatus::WAITING, false],
            [ProcessStatus::FAILED, ProcessStatus::ENDING, false],
            [ProcessStatus::FAILED, ProcessStatus::FAILED, true],
            [ProcessStatus::FAILED, ProcessStatus::COMPLETED, false],

            [ProcessStatus::COMPLETED, ProcessStatus::STARTING, false],
            [ProcessStatus::COMPLETED, ProcessStatus::RUNNING, false],
            [ProcessStatus::COMPLETED, ProcessStatus::WAITING, false],
            [ProcessStatus::COMPLETED, ProcessStatus::ENDING, false],
            [ProcessStatus::COMPLETED, ProcessStatus::FAILED, false],
            [ProcessStatus::COMPLETED, ProcessStatus::COMPLETED, true],
        ];

    }

    /**
     * @test
     * @dataProvider can_manage_transitions_provider
     */
    public function can_manage_transitions($from, $to, $allowed)
    {
        $processStatus = new ProcessStatus($from);

        if ($allowed) {
            $processStatus = $processStatus->change(new ProcessStatus($to));
            $this->assertEquals($to, $processStatus->value());
        } else {
            try {
                $processStatus->change(new ProcessStatus($to));
                $this->fail();
            } catch (\Throwable $e) {
                $this->assertEquals(sprintf('Status transition from "%s" to "%s" is not allowed', $from, $to), $e->getMessage());
            }
        }

    }


}
