<?php

namespace JobBoy\Process\Domain\IterationMaker\Events;

use JobBoy\Process\Domain\Event\Message\HasMessageInterface;
use JobBoy\Process\Domain\Event\Message\Message;

class ProcessManagementReleased implements HasMessageInterface
{

    private $message;

    public function __construct()
    {
        $this->message = new Message("Process management released");
    }

    public function message(): Message
    {
        return $this->message;
    }

}