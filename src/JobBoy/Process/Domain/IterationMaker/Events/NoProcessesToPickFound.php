<?php

namespace JobBoy\Process\Domain\IterationMaker\Events;

use JobBoy\Process\Domain\Event\Message\HasMessageInterface;
use JobBoy\Process\Domain\Event\Message\Message;

class NoProcessesToPickFound implements HasMessageInterface
{
    private $message;

    public function __construct()
    {
        $this->message = new Message('No processes to pick found');
    }

    public function message(): Message
    {
        return $this->message;
    }

}