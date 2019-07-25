<?php

namespace JobBoy\Process\Domain\Entity\Factory;

use JobBoy\Process\Domain\Entity\Data\ProcessData;
use JobBoy\Process\Domain\Entity\Id\ProcessId;
use JobBoy\Process\Domain\Entity\Process;
use Ramsey\Uuid\Uuid;

class ProcessFactory
{

    /** @var string */
    protected $entityClass;

    public function __construct(string $entityClass = Process::class)
    {
        $this->entityClass = $entityClass;
    }

    public function entityClass(): string
    {
        return $this->entityClass;
    }

    public function create(ProcessData $data): Process
    {
        if (!$data->id()) {
            $data->setId(new ProcessId(Uuid::uuid4()));
        }

        return call_user_func($this->entityClass . '::create', $data);
    }

}