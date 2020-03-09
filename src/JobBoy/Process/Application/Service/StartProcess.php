<?php

namespace JobBoy\Process\Application\Service;

use JobBoy\Process\Domain\Entity\Data\ProcessData;
use JobBoy\Process\Domain\Entity\Factory\ProcessFactory;
use JobBoy\Process\Domain\Entity\Id\ProcessId;
use JobBoy\Process\Domain\Event\EventBusInterface;
use JobBoy\Process\Domain\Event\NullEventBus;
use JobBoy\Process\Domain\Events\Process\ProcessCreated;
use JobBoy\Process\Domain\ProcessParameters;
use JobBoy\Process\Domain\Repository\ProcessRepositoryInterface;
use Ramsey\Uuid\Uuid;

class StartProcess
{

    /** @var ProcessFactory */
    protected $processFactory;
    /** @var ProcessRepositoryInterface */
    protected $processRepository;
    /** @var EventBusInterface */
    protected $eventBus;

    public function __construct(
        ProcessFactory $processFactory,
        ProcessRepositoryInterface $processRepository,
        ?EventBusInterface $eventBus = null
    )
    {
        if (!$eventBus) {
            $eventBus = new NullEventBus();
        }

        $this->processFactory = $processFactory;
        $this->processRepository = $processRepository;
        $this->eventBus = $eventBus;
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

        $this->eventBus->publish(new ProcessCreated($process->id()));

    }

}