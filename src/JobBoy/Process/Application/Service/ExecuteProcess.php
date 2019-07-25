<?php

namespace JobBoy\Process\Application\Service;

use JobBoy\Process\Domain\Entity\Data\ProcessData;
use JobBoy\Process\Domain\Entity\Factory\ProcessFactory;
use JobBoy\Process\Domain\Entity\Id\ProcessId;
use JobBoy\Process\Domain\IterationMaker\Exception\IteratingYetException;
use JobBoy\Process\Domain\IterationMaker\IterationMaker;
use JobBoy\Process\Domain\ProcessParameters;
use JobBoy\Process\Domain\Repository\ProcessRepositoryInterface;
use Ramsey\Uuid\Uuid;

class ExecuteProcess
{
    const IDLE_TIME = 5;

    /** @var ProcessFactory */
    protected $processFactory;
    /** @var ProcessRepositoryInterface */
    protected $processRepository;
    /** @var IterationMaker */
    protected $iterationMaker;

    public function __construct(
        ProcessFactory $processFactory,
        ProcessRepositoryInterface $processRepository,
        IterationMaker $iterationMaker
    )
    {
        $this->processRepository = $processRepository;
        $this->processFactory = $processFactory;
        $this->iterationMaker = $iterationMaker;
    }

    public function execute(string $code, array $parameters = []): void
    {
        $process = $this->processFactory->create(
            (new ProcessData())
                ->setId(new ProcessId(Uuid::uuid4()))
                ->setCode($code)
                ->setParameters(new ProcessParameters($parameters))
        );

        $this->processRepository->add($process);

        $id = $process->id();

        while ($process->status()->isActive()) {
            try {
                $response = $this->iterationMaker->work();
                if (!$response->hasWorked()) {
                    sleep(self::IDLE_TIME);
                }
            } catch (IteratingYetException $e) {
                sleep(self::IDLE_TIME);
            }
            $process = $this->processRepository->byId($id);
        }
    }

}