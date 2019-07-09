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
            $this->assertEquals('Invalid process status value "an_invalid_value"', $e->getMessage());
        }

    }


    public function can_be_created_provider()
    {
        return [
            [ProcessStatus::STARTING, 'starting', 'isStarting'],
            [ProcessStatus::RUNNING, 'running', 'isRunning'],
            [ProcessStatus::FAILING, 'failing', 'isFailing'],
            [ProcessStatus::FAILED, 'failed', 'isFailed'],
            [ProcessStatus::ENDING, 'ending', 'isEnding'],
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
            [ProcessStatus::FAILING, true, true],
            [ProcessStatus::FAILED, false, false],
            [ProcessStatus::ENDING, true, true],
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
            [ProcessStatus::STARTING, ProcessStatus::FAILING, true],
            [ProcessStatus::STARTING, ProcessStatus::FAILED, true],
            [ProcessStatus::STARTING, ProcessStatus::ENDING, true],
            [ProcessStatus::STARTING, ProcessStatus::COMPLETED, true],

            [ProcessStatus::RUNNING, ProcessStatus::STARTING, false],
            [ProcessStatus::RUNNING, ProcessStatus::RUNNING, true],
            [ProcessStatus::RUNNING, ProcessStatus::FAILING, true],
            [ProcessStatus::RUNNING, ProcessStatus::FAILED, true],
            [ProcessStatus::RUNNING, ProcessStatus::ENDING, true],
            [ProcessStatus::RUNNING, ProcessStatus::COMPLETED, true],

            [ProcessStatus::FAILING, ProcessStatus::STARTING, false],
            [ProcessStatus::FAILING, ProcessStatus::RUNNING, false],
            [ProcessStatus::FAILING, ProcessStatus::FAILING, true],
            [ProcessStatus::FAILING, ProcessStatus::FAILED, true],
            [ProcessStatus::FAILING, ProcessStatus::ENDING, false],
            [ProcessStatus::FAILING, ProcessStatus::COMPLETED, false],

            [ProcessStatus::FAILED, ProcessStatus::STARTING, false],
            [ProcessStatus::FAILED, ProcessStatus::RUNNING, false],
            [ProcessStatus::FAILED, ProcessStatus::FAILING, false],
            [ProcessStatus::FAILED, ProcessStatus::FAILED, true],
            [ProcessStatus::FAILED, ProcessStatus::ENDING, false],
            [ProcessStatus::FAILED, ProcessStatus::COMPLETED, false],

            [ProcessStatus::ENDING, ProcessStatus::STARTING, false],
            [ProcessStatus::ENDING, ProcessStatus::RUNNING, false],
            [ProcessStatus::ENDING, ProcessStatus::FAILING, true],
            [ProcessStatus::ENDING, ProcessStatus::FAILED, true],
            [ProcessStatus::ENDING, ProcessStatus::ENDING, true],
            [ProcessStatus::ENDING, ProcessStatus::COMPLETED, true],

            [ProcessStatus::COMPLETED, ProcessStatus::STARTING, false],
            [ProcessStatus::COMPLETED, ProcessStatus::RUNNING, false],
            [ProcessStatus::COMPLETED, ProcessStatus::FAILING, false],
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
