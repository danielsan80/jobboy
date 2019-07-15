<?php

namespace JobBoy\Process\Domain\ProcessIterator\ProcessHandlers;

use JobBoy\Process\Domain\Entity\Id\ProcessId;
use JobBoy\Process\Domain\ProcessIterator\AbstractProcessHandler;
use JobBoy\Process\Domain\ProcessIterator\Exception\UnsupportedProcessException;
use JobBoy\Process\Domain\ProcessIterator\IterationResponse;
use JobBoy\Process\Domain\ProcessIterator\ProcessHandlerInterface;
use JobBoy\Process\Domain\ProcessIterator\ProcessHandlerRegistry;
use JobBoy\Process\Domain\Repository\ProcessRepositoryInterface;

class CodeProcessHandler extends AbstractProcessHandler implements ProcessHandlerInterface
{
    /** @var ProcessHandlerRegistry */
    protected $processHandlerRegistry;
    /** @var string */
    protected $code;

    public function __construct(
        ProcessRepositoryInterface $processRepository,
        ProcessHandlerRegistry $processHandlerRegistry,
        string $code)
    {
        parent::__construct($processRepository);
        $this->processHandlerRegistry = $processHandlerRegistry;
        $this->code = $code;
    }

    public function supports(ProcessId $id): bool
    {
        if ($this->process($id)->code()!==$this->code) {
            return false;
        }

        try {
            $handler = $this->processHandlerRegistry->get($id, $this->code);
            return true;
        } catch (UnsupportedProcessException $e) {
            return false;
        }
    }

    public function handle(ProcessId $id): IterationResponse
    {
        $handler = $this->processHandlerRegistry->get($id, $this->code);

        return $handler->handle($id);
    }
}