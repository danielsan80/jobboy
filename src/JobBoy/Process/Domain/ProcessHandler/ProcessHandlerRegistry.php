<?php

namespace JobBoy\Process\Domain\ProcessHandler;

use JobBoy\Process\Domain\Entity\Id\ProcessId;

class ProcessHandlerRegistry
{
    /** @var ProcessHandlerInterface[][] */
    protected $handlers = [];
    protected $frozen = false;

    public function add(ProcessHandlerInterface $processHandler, $priority = null)
    {
        if ($this->frozen) {
            throw new \LogicException('The registry is frozen. You cannot add anything.');
        }
        if (is_null($priority)) {
            $priority = 100;
        }
        $this->handlers[$priority][] = $processHandler;

        return $this;
    }

    public function get(ProcessId $id): ProcessHandlerInterface
    {
        $this->ensureHandlersAreSorted();

        foreach ($this->handlers as $handlers) {
            foreach ($handlers as $handler) {
                if ($handler->supports($id)) {
                    return $handler;
                }
            }
        }
        throw new \LogicException(sprintf('No ProcessHandlers supports the process "%s"', $id));
    }

    private function ensureHandlersAreSorted()
    {
        if (!$this->frozen) {
            ksort($this->handlers);
            $this->frozen = true;
        }
    }

}