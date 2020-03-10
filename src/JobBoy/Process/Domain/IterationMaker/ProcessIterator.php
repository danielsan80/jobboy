<?php

namespace JobBoy\Process\Domain\IterationMaker;

use JobBoy\Process\Domain\Entity\Id\ProcessId;
use JobBoy\Process\Domain\Event\EventBusInterface;
use JobBoy\Process\Domain\Event\NullEventBus;
use JobBoy\Process\Domain\Events\Iteration\IterationEnded;
use JobBoy\Process\Domain\Events\Iteration\IterationFailed;
use JobBoy\Process\Domain\Events\Iteration\IterationStarted;
use JobBoy\Process\Domain\ProcessHandler\IterationResponse;
use JobBoy\Process\Domain\ProcessHandler\ProcessHandlerRegistry;
use JobBoy\Process\Domain\Repository\ProcessRepositoryInterface;

class ProcessIterator
{

    /** @var ProcessHandlerRegistry  */
    protected $registry;
    /** @var ProcessRepositoryInterface */
    protected $processRepository;
    /** @var EventBusInterface|null */
    protected $eventBus;

    public function __construct(
        ProcessHandlerRegistry $registry,
        ProcessRepositoryInterface $processRepository,
        ?EventBusInterface $eventBus = null
    )
    {
        if (!$eventBus) {
            $eventBus = new NullEventBus();
        }

        $this->registry = $registry;
        $this->processRepository = $processRepository;
        $this->eventBus = $eventBus;
    }

    public function iterate(ProcessId $id): IterationResponse
    {
        $handler = $this->registry->get($id);
        $process = $this->processRepository->byId($id);
        $process->handle();

        $this->eventBus->publish(new IterationStarted($id));

        try {
            $response = $handler->handle($id);
        } catch (\Throwable $e) {
            $process = $this->processRepository->byId($id);
            $process->release();
            $this->eventBus->publish(new IterationFailed($id, $e));
            throw $e;
        }

        $process = $this->processRepository->byId($id);
        $process->release();
        $this->eventBus->publish(new IterationEnded($id));
        return $response;
    }

}