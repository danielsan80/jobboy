<?php

namespace JobBoy\Process\Application\Service\Events;

use JobBoy\Process\Domain\Event\Message\HasMessageInterface;
use JobBoy\Process\Domain\Event\Message\Message;
use JobBoy\Process\Domain\MemoryControl\Util;

class MemoryLimitExceeded implements HasMessageInterface
{
    /** @var Message  */
    protected $message;

    public function __construct(int $usage)
    {
        $this->message = new Message('Memory limit exceeded', ['usage' => Util::bytesToString($usage)]);
    }

    public function message(): Message
    {
        return $this->message;
    }
}