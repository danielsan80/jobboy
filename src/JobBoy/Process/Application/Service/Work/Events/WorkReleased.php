<?php

namespace JobBoy\Process\Application\Service\Work\Events;

use JobBoy\Process\Domain\Event\Message\HasMessageInterface;
use JobBoy\Process\Domain\Event\Message\Message;

class WorkReleased implements HasMessageInterface
{
    /** @var Message  */
    protected $message;

    public function __construct()
    {
        $this->message = new Message('Work service released');
    }

    public function message(): Message
    {
        return $this->message;
    }

}