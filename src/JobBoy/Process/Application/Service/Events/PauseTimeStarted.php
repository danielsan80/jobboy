<?php

namespace JobBoy\Process\Application\Service\Events;

use JobBoy\Process\Domain\Event\Message\HasMessageInterface;
use JobBoy\Process\Domain\Event\Message\Message;

class PauseTimeStarted implements HasMessageInterface
{
    /** @var Message */
    protected $message;

    public function __construct(int $idleTime)
    {
        $this->message = new Message('Work service is in pause. Pause time for {{seconds}} seconds', [
            'seconds' => $idleTime
        ]);
    }

    public function message(): Message
    {
        return $this->message;
    }
}