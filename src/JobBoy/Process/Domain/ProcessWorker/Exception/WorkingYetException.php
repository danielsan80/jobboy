<?php

namespace JobBoy\Process\Domain\ProcessWorker\Exception;

class WorkingYetException extends \RuntimeException
{
    public function __construct()
    {
        parent::__construct('I\'m working yet');
    }

}