<?php

namespace JobBoy\Flow\Domain\ProcessHandler;

use JobBoy\Flow\Domain\FlowManager\FlowManager;
use JobBoy\Flow\Domain\FlowManager\HasNode;
use JobBoy\Process\Domain\Entity\Id\ProcessId;
use JobBoy\Process\Domain\Entity\Process;
use JobBoy\Process\Domain\ProcessHandler\ProcessHandlerInterface;
use JobBoy\Process\Domain\Repository\ProcessRepositoryInterface;

abstract class AbstractFlowProcessHandler implements ProcessHandlerInterface, HasNode
{
    protected $processRepository;
    protected $flowManager;


    public function __construct(
        ProcessRepositoryInterface $processRepository,
        FlowManager $flowManager
    )
    {
        $this->processRepository = $processRepository;
        $this->flowManager = $flowManager;
    }

    public function supports(ProcessId $id): bool
    {
        return !$this->process($id)->isHandled()
            && $this->process($id)->status()->isRunning()
            && $this->process($id)->code() === $this->node()->job()
            && $this->flowManager->atNode($id, $this->node()->code());
    }

    protected function process(ProcessId $id): ?Process
    {
        return $this->processRepository->byId($id);
    }

    protected function changeNode(ProcessId $id, string $on): void
    {
        $this->flowManager->changeNode($id, $on);
    }

}