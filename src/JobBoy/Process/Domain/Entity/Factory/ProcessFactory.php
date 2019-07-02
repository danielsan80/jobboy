<?php

namespace JobBoy\Process\Domain\Entity\Factory;

use JobBoy\Process\Domain\Entity\Data\CreateProcessData;
use JobBoy\Process\Domain\Entity\Process;

class ProcessFactory
{

    /** @var string */
    protected $entityClass;

    public function __construct(string $entityClass)
    {
        $this->entityClass = $entityClass;
    }

    public function entityClass(): string
    {
        return $this->entityClass;
    }

    public function create(CreateProcessData $data): Process
    {
        return new $this->entityClass($data);
    }

    public function denormalize(array $array): Process
    {
        return call_user_func($this->entityClass . '::normalize', [$array]);
    }

}