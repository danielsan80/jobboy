<?php

namespace JobBoy\Process\Application\Service;

use JobBoy\Process\Domain\Entity\Data\ProcessData;
use JobBoy\Process\Domain\Entity\Factory\ProcessFactory;
use JobBoy\Process\Domain\ProcessParameters;
use JobBoy\Process\Domain\Repository\ProcessRepositoryInterface;

class StartProcess
{

    /** @var ProcessFactory */
    protected $processFactory;
    /** @var ProcessRepositoryInterface */
    protected $processRepository;

    public function __construct(
        ProcessFactory $processFactory,
        ProcessRepositoryInterface $processRepository
    )
    {
        $this->processFactory = $processFactory;
        $this->processRepository = $processRepository;
    }

    public function execute(string $code, array $parameters = []): void
    {
        $process = $this->processFactory->create(
            (new ProcessData())
                ->setCode($code)
                ->setParameters(new ProcessParameters($parameters))
        );

        $this->processRepository->add($process);

    }

}