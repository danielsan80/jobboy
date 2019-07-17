<?php

namespace JobBoy\Process\Domain\Event\Message;

interface HasMessageInterface
{
    public function message(): Message;
}