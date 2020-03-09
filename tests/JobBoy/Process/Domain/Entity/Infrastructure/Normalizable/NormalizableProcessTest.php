<?php

namespace Tests\JobBoy\Process\Domain\Entity\Infrastructure\Normalizable;

use JobBoy\Clock\Domain\Clock;
use JobBoy\Clock\Domain\Infrastructure\Carbon\CarbonTimeFactory;
use JobBoy\Process\Domain\Entity\Id\ProcessId;
use JobBoy\Process\Domain\Entity\Infrastructure\Normalizable\Data\NormalizableProcessData;
use JobBoy\Process\Domain\Entity\Infrastructure\Normalizable\NormalizableProcess;
use JobBoy\Process\Domain\ProcessParameters;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Uuid;

class NormalizableProcessTest extends TestCase
{

    /**
     * @test
     */
    public function a_process_scenario()
    {
        $timeFactory = new CarbonTimeFactory();
        Clock::setTimeFactory($timeFactory);

        $now = new \DateTimeImmutable('2019-01-01 00:00:00 +0100');
        $timeFactory->freeze($now);

        $id = Uuid::uuid4();

        $process = NormalizableProcess::create(
            (new NormalizableProcessData())
                ->setId(new ProcessId($id))
                ->setCode('a_code')
                ->setParameters(new ProcessParameters(['a_key' => 'a_value']))
        );


        $this->assertSame((string)$id, (string)$process->id());
        $this->assertSame('a_code', (string)$process->code());
        $this->assertSame(['a_key' => 'a_value'], $process->parameters()->toScalar());
        $this->assertTrue($process->status()->isStarting());
        $this->assertSameDateTime($now, $process->createdAt());
        $this->assertSameDateTime($now, $process->updatedAt());
        $this->assertNull($process->startedAt());
        $this->assertNull($process->endedAt());
        $this->assertNull($process->handledAt());
        $this->assertNull($process->killedAt());


        $normalizedProcess = $process->normalize();

        $this->assertSame([
            'id' => (string)$id,
            'code' => 'a_code',
            'parameters' => ['a_key' => 'a_value'],
            'status' => 'starting',
            'created_at' => '2019-01-01T00:00:00+0100',
            'updated_at' => '2019-01-01T00:00:00+0100',
            'started_at' => null,
            'ended_at' => null,
            'handled_at' => null,
            'killed_at' => null,
            'store' => []
        ], $normalizedProcess);

        $denormalizedProcess = NormalizableProcess::denormalize($normalizedProcess);

        $this->assertSame([
            'id' => (string)$id,
            'code' => 'a_code',
            'parameters' => ['a_key' => 'a_value'],
            'status' => 'starting',
            'created_at' => '2019-01-01T00:00:00+0100',
            'updated_at' => '2019-01-01T00:00:00+0100',
            'started_at' => null,
            'ended_at' => null,
            'handled_at' => null,
            'killed_at' => null,
            'store' => []
        ], $denormalizedProcess->normalize());

        Clock::resetTimeFactory();
    }

    protected function assertSameDateTime(\DateTimeImmutable $expected, ?\DateTimeImmutable $actual)
    {
        $this->assertNotNull($actual);
        $this->assertSame(
            $expected->format(\DateTime::ISO8601),
            $actual->format(\DateTime::ISO8601)
        );
    }


}
