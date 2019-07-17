<?php


namespace JobBoy\Process\Domain\Event;

class SimpleEventBus implements EventBusInterface
{
    private $eventListeners = [];
    private $queue = [];
    private $isPublishing = false;

    public function subscribe(EventListenerInterface $eventListener): void
    {
        $this->eventListeners[] = $eventListener;
    }

    public function unsubscribe(EventListenerInterface $eventListener): void
    {
        foreach ($this->eventListeners as $i => $subscribedEventListener) {
            if ($eventListener===$subscribedEventListener) {
                unset($this->eventListeners[$i]);
                return;
            }
        }
        throw new \LogicException('This EventListener is not subscribed');
    }

    public function publish($event): void
    {
        $this->queue[] = $event;
        if (!$this->isPublishing) {
            $this->isPublishing = true;
            try {
                while ($event = array_shift($this->queue)) {
                    foreach ($this->eventListeners as $eventListener) {
                        $eventListener->handle($event);
                    }
                }

            } finally {
                $this->isPublishing = false;
            }
        }
    }
}