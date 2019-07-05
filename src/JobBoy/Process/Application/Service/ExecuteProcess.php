<?php

namespace JobBoy\Process\Application\Service;

use JobBoy\Process\Domain\Entity\Data\ProcessData;
use JobBoy\Process\Domain\Entity\Factory\ProcessFactory;
use JobBoy\Process\Domain\ProcessIterator\Exception\IteratingYetException;
use JobBoy\Process\Domain\ProcessIterator\ProcessIterator;
use JobBoy\Process\Domain\ProcessParameters;
use JobBoy\Process\Domain\Repository\ProcessRepositoryInterface;

class ExecuteProcess
{
    const IDLE_TIME = 5;

    /** @var ProcessFactory */
    protected $processFactory;
    /** @var ProcessRepositoryInterface */
    protected $processRepository;
    /** @var ProcessIterator */
    protected $processIterator;

    public function __construct(
        ProcessFactory $processFactory,
        ProcessRepositoryInterface $processRepository,
        ProcessIterator $processIterator
    )
    {
        $this->processRepository = $processRepository;
        $this->processIterator = $processIterator;
        $this->processFactory = $processFactory;
    }

    public function execute(string $code, array $parameters = []): void
    {
        $process = $this->processFactory->create(
            (new ProcessData())
                ->setCode($code)
                ->setParameters(new ProcessParameters($parameters))
        );

        $this->processRepository->add($process);

        $id = $process->id();

        while ($process->status()->isActive()) {
            try {
                $this->processIterator->work();
            } catch (IteratingYetException $e) {
                sleep(self::IDLE_TIME);
            }
            $process = $this->processRepository->byId($id);
        }
    }

}