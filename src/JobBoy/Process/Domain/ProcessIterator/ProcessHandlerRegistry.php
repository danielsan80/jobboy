<?php

namespace JobBoy\Process\Domain\ProcessIterator;

use JobBoy\Process\Domain\Entity\Id\ProcessId;
use JobBoy\Process\Domain\ProcessIterator\Exception\UnsupportedProcessException;

class ProcessHandlerRegistry
{
    const DEFAULT_PRIORITY = 100;
    const DEFAULT_CHANNEL = 'default';

    /** @var ProcessHandlerInterface[][][] */
    protected $handlers = [];
    protected $frozen = [];

    public function add(ProcessHandlerInterface $processHandler, ?int $priority = null, ?string $channel = null)
    {
        if ($this->frozen) {
            throw new \LogicException('The registry is frozen. You cannot add anything.');
        }
        if ($priority === null) {
            $priority = self::DEFAULT_PRIORITY;
        }
        if ($channel === null) {
            $channel = self::DEFAULT_CHANNEL;
        }
        $this->handlers[$channel][$priority][] = $processHandler;

        return $this;
    }

    public function get(ProcessId $id, ?string $channel = null): ProcessHandlerInterface
    {
        if ($channel === null) {
            $channel = self::DEFAULT_CHANNEL;
        }

        $this->ensureHandlersAreSorted($channel);

        foreach ($this->handlers[$channel] as $handlers) {
            foreach ($handlers as $handler) {
                if ($handler->supports($id)) {
                    return $handler;
                }
            }
        }
        throw new UnsupportedProcessException(sprintf(
            'No ProcessHandlers supports the process "%s" on channel "%s"',
            $id, $channel));
    }

    private function ensureHandlersAreSorted(string $channel): void
    {
        if (isset($this->frozen[$channel])) {
            return;
        }

        if (!key_exists($channel, $this->handlers)) {
            throw new UnsupportedProcessException(sprintf(
                'No ProcessHandlers supports the channel "%s"',
                $channel
            ));
        }

        ksort($this->handlers[$channel]);
        $this->frozen[$channel] = true;
    }

}