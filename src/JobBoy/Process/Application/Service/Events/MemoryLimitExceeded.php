<?php

namespace JobBoy\Process\Application\Service\Events;

use JobBoy\Process\Domain\Event\Message\HasMessageInterface;
use JobBoy\Process\Domain\Event\Message\Message;

class MemoryLimitExceeded implements HasMessageInterface
{
    /** @var Message  */
    protected $message;

    public function __construct()
    {
        $this->message = new Message('MemoryLimitExceeded', []);
    }

    public function message(): Message
    {
        return $this->message;
    }
}