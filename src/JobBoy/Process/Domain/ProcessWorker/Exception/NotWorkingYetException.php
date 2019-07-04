<?php

namespace JobBoy\Process\Domain\ProcessWorker\Exception;

class NotWorkingYetException extends \RuntimeException
{
    public function __construct()
    {
        parent::__construct('I\'m not working yet');
    }

}