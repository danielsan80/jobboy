<?php

namespace JobBoy\Flow\Domain\FlowManager;

use Assert\Assertion;
use JobBoy\Process\Domain\Entity\Id\ProcessId;
use JobBoy\Process\Domain\Entity\Process;
use JobBoy\Process\Domain\Repository\ProcessRepositoryInterface;

class FlowManager
{
    const NODE = 'node';

    protected $processRepository;
    private $transitionRegistry;

    public function __construct(
        ProcessRepositoryInterface $processRepository,
        TransitionRegistry $transitionRegistry
    )
    {
        $this->processRepository = $processRepository;
        $this->transitionRegistry = $transitionRegistry;
    }

    protected function process(ProcessId $id): ?Process
    {
        return $this->processRepository->byId($id);
    }

    public function reset(ProcessId $id): void
    {
        $process = $this->process($id);

        $transition = $this->transitionRegistry->getEntry($process->code());

        $this->process($id)->set(self::NODE, $transition->to()->code());
    }

    public function atNode(ProcessId $id, string $node): bool
    {
        return $this->process($id)->get(self::NODE) === $node;
    }


    public function changeNode(ProcessId $id, string $on): ?string
    {
        Assertion::notBlank($on);

        $currentNode = $this->currentNode($id);
        $transition = $this->transitionRegistry->get(Node::fromArray([
            'job' => $this->job($id),
            'code' => $currentNode,
        ]), $on);

        if ($transition->isNodeChange()) {
            $this->process($id)->set(self::NODE, $transition->to()->code());
            return $transition->to()->code();
        }
        if ($transition->isExit()) {
            $this->process($id)->unset(self::NODE);
            return null;
        }
    }

    public function isWalking(ProcessId $id): bool
    {
        return (bool)$this->currentNode($id);
    }

    protected function currentNode(ProcessId $id): ?string
    {
        return $this->process($id)->get(self::NODE);
    }

    protected function job(ProcessId $id): string
    {
        return $this->process($id)->code();
    }

}