<?php

namespace JobBoy\Process\Domain\ProcessIterator\ProcessHandlers\Base;

use JobBoy\Process\Domain\Entity\Id\ProcessId;
use JobBoy\Process\Domain\ProcessIterator\ProcessHandlerInterface;

/**
 * It is a generic base for all ProcessHandlers who manages the remained handled processes.
 * Just implement the doSupports() method.
 */
abstract class AbstractHandledProcessHandler extends AbstractProcessHandler implements ProcessHandlerInterface
{

    public function supports(ProcessId $id): bool
    {
        return $this->process($id)->isHandled() && $this->doSupports($id);
    }

    abstract protected function doSupports(ProcessId $id): bool;


}