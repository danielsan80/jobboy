<?php

namespace JobBoy\Process\Domain\Event\Message;

class Message
{

    /** @var string */
    protected $text;

    /** @var array */
    protected $parameters;

    public function __construct(string $text, array $parameters = [])
    {
        $this->text = $text;
        $this->parameters = $parameters;
    }

    public function text(): string
    {
        return $this->text;
    }

    public function parameters(): array
    {
        return $this->parameters;
    }

}