<?php

namespace JobBoy\Process\Application\Service;

use JobBoy\Process\Domain\Entity\Data\ProcessData;
use JobBoy\Process\Domain\Entity\Factory\ProcessFactory;
use JobBoy\Process\Domain\Entity\Id\ProcessId;
use JobBoy\Process\Domain\ProcessHandler\MainProcessHandler;
use JobBoy\Process\Domain\ProcessParameters;
use JobBoy\Process\Domain\Repository\ProcessRepositoryInterface;

class ExecuteProcess
{

    /** @var ProcessFactory */
    protected $processFactory;
    /** @var ProcessRepositoryInterface */
    protected $processRepository;
    /** @var MainProcessHandler */
    protected $mainProcessHandler;

    public function __construct(
        ProcessFactory $processFactory,
        ProcessRepositoryInterface $processRepository,
        MainProcessHandler $mainProcessHandler
    )
    {
        $this->processRepository = $processRepository;
        $this->mainProcessHandler = $mainProcessHandler;
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



        while ($process->status()->isActive()) {
            // $this->processWorker()->work();
//            $this->mainProcessHandler->handle($id);
            $process = $this->processRepository->byId($id);
        }
    }

}