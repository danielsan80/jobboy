<?php

namespace JobBoy\Process\Domain\ProcessHandler\ProcessHandlers\Common;

use JobBoy\Process\Domain\Entity\Id\ProcessId;
use JobBoy\Process\Domain\ProcessHandler\Exception\UnsupportedProcessException;
use JobBoy\Process\Domain\ProcessHandler\IterationResponse;
use JobBoy\Process\Domain\ProcessHandler\ProcessHandlerInterface;
use JobBoy\Process\Domain\ProcessHandler\ProcessHandlerRegistry;
use JobBoy\Process\Domain\ProcessHandler\ProcessHandlers\Base\AbstractProcessHandler;
use JobBoy\Process\Domain\Repository\ProcessRepositoryInterface;

/**
 * It is a generic ProcessHandler who support ALL Process of a certain code. Then it delegate the handling to other
 * ProcessHandlers registered of the `<code>` channel.
 */
class HandleCodeProcess extends AbstractProcessHandler implements ProcessHandlerInterface
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