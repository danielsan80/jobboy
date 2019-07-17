<?php

namespace JobBoy\Process\Application\Service\Events;

use JobBoy\Process\Domain\Event\Message\HasMessageInterface;
use JobBoy\Process\Domain\Event\Message\Message;

class Timedout implements HasMessageInterface
{
    /** @var Message  */
    protected $message;

    public function __construct(int $timeout)
    {
        $this->message = new Message('Timeout: {{seconds}} seconds', [
            'seconds' => $timeout
        ]);
    }

    public function message(): Message
    {
        return $this->message;
    }
}