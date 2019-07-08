<?php

namespace Tests\JobBoy\Process\Application\Service;

use JobBoy\Process\Domain\IterationMaker\IterationMaker;
use JobBoy\Process\Domain\ProcessIterator\IterationResponse;
use PHPUnit\Framework\TestCase;

use JobBoy\Process\Application\Service\IterateOneProcess;

class IterateOneProcessTest extends TestCase
{

    /**
     * @test
     */
    public function it_works()
    {

        $iterationMaker = \Mockery::mock(IterationMaker::class);
        $iterationMaker->shouldReceive('work')
            ->once()
            ->andReturnUsing(function() {
                return new IterationResponse(true);
            });

        $service = new IterateOneProcess($iterationMaker);

        $service->execute();

        $this->assertTrue(true);

    }

}
