<?php

namespace JobBoy\Process\Domain\ProcessHandler\ProcessHandlers\Base;

use JobBoy\Process\Domain\Entity\Id\ProcessId;
use JobBoy\Process\Domain\ProcessHandler\ProcessHandlerInterface;

/**
 * It is a generic base for all ProcessHandlers who manages the NOT remained handled processes, the normal way.
 * Just implement the doSupports() method.
 */
abstract class AbstractUnhandledProcessHandler extends AbstractProcessHandler implements ProcessHandlerInterface
{

    public function supports(ProcessId $id): bool
    {
        return !$this->process($id)->isHandled() && $this->doSupports($id);
    }

    abstract protected function doSupports(ProcessId $id): bool;


}