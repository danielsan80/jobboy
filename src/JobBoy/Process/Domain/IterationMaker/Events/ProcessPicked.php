<?php

namespace JobBoy\Process\Domain\IterationMaker\Events;

use JobBoy\Process\Domain\Entity\Id\ProcessId;
use JobBoy\Process\Domain\Event\Message\HasMessageInterface;
use JobBoy\Process\Domain\Event\Message\Message;

class ProcessPicked implements HasMessageInterface
{
    private $message;

    public function __construct(ProcessId $id, string $code, string $type)
    {
        $this->message = new Message('"{{type}}" process of code "{{code}}"({{id}}) picked', [
            'type' => $type,
            'id' => $id->toScalar(),
            'code' => $code,
        ]);
    }

    public function message(): Message
    {
        return $this->message;
    }


}