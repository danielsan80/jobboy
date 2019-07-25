<?php

namespace JobBoy\Process\Domain\ProcessHandler\ProcessHandlers\Base;

use JobBoy\Process\Domain\Entity\Id\ProcessId;
use JobBoy\Process\Domain\ProcessHandler\ProcessHandlerInterface;
use JobBoy\Process\Domain\Repository\ProcessRepositoryInterface;

/**
 * It is a generic base for all ProcessHandlers who manages the processes of a certain code.
 * Just implement the doSupports() method.
 */
abstract class AbstractByCodeProcessHandler extends AbstractProcessHandler implements ProcessHandlerInterface
{
    /** @var string */
    protected $code;

    public function __construct(
        ProcessRepositoryInterface $processRepository,
        string $code)
    {
        parent::__construct($processRepository);
        $this->code = $code;
    }

    public function supports(ProcessId $id): bool
    {
        return !$this->process($id)->code() === $this->code && $this->doSupports($id);
    }

    abstract protected function doSupports(ProcessId $id): bool;


}