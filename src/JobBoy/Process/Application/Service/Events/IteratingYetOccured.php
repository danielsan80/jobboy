<?php

namespace JobBoy\Process\Application\Service\Events;

use JobBoy\Process\Domain\Event\Message\HasMessageInterface;
use JobBoy\Process\Domain\Event\Message\Message;

class IteratingYetOccured implements HasMessageInterface
{
    /** @var Message  */
    protected $message;

    public function __construct()
    {
        $this->message = new Message('Someone is iterating yet.');
    }

    public function message(): Message
    {
        return $this->message;
    }
}