<?php

namespace JobBoy\Process\Application\Service\Exception;

class WorkIsNotRunningYetException extends \DomainException
{
    public function __construct()
    {
        parent::__construct('Work service is not running yet');
    }
}