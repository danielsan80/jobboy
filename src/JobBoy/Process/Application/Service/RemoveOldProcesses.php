<?php

namespace JobBoy\Process\Application\Service;

use JobBoy\Process\Domain\Event\EventBusInterface;
use JobBoy\Process\Domain\Event\NullEventBus;
use JobBoy\Process\Domain\Events\Process\ProcessRemoved;
use JobBoy\Process\Domain\Repository\Infrastructure\Util\ProcessRepositoryUtil;
use JobBoy\Process\Domain\Repository\ProcessRepositoryInterface;

class RemoveOldProcesses
{

    /** @var ProcessRepositoryInterface */
    protected $processRepository;
    /** @var EventBusInterface|null */
    protected $eventBus;

    public function __construct(
        ProcessRepositoryInterface $processRepository,
        ?EventBusInterface $eventBus = null
    )
    {
        if (!$eventBus) {
            $eventBus = new NullEventBus();
        }

        $this->processRepository = $processRepository;
        $this->eventBus = $eventBus;
    }

    public function execute(int $days = 90): void
    {

        $processes = $this->processRepository->stale(ProcessRepositoryUtil::aFewDaysAgo($days));

        foreach ($processes as $process) {
            $this->processRepository->remove($process);
            $this->eventBus->publish(new ProcessRemoved($process->id()));
        }
    }

}