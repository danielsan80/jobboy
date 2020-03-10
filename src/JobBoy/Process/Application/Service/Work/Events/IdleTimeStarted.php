<?php

namespace JobBoy\Process\Application\Service\Work\Events;

use JobBoy\Process\Domain\Event\Message\HasMessageInterface;
use JobBoy\Process\Domain\Event\Message\Message;

class IdleTimeStarted implements HasMessageInterface
{
    /** @var Message  */
    protected $message;

    public function __construct(int $idleTime)
    {
        $this->message = new Message('Idle time for {{seconds}} seconds', [
            'seconds' => $idleTime
        ]);
    }

    public function message(): Message
    {
        return $this->message;
    }
}