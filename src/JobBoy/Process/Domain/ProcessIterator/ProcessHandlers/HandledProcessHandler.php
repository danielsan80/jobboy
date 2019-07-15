<?php

namespace JobBoy\Process\Domain\ProcessIterator\ProcessHandlers;

use JobBoy\Process\Domain\Entity\Id\ProcessId;
use JobBoy\Process\Domain\ProcessIterator\AbstractProcessHandler;
use JobBoy\Process\Domain\ProcessIterator\ProcessHandlerInterface;

abstract class HandledProcessHandler extends AbstractProcessHandler implements ProcessHandlerInterface
{

    final public function supports(ProcessId $id): bool
    {
        return $this->process($id)->isHandled() && $this->doSupports($id);
    }

    abstract protected function doSupports(ProcessId $id): bool;


}