<?php

namespace JobBoy\Process\Application\Service\Work\Exception;

class WorkRunningYetException extends \DomainException
{
    public function __construct()
    {
        parent::__construct('Work service is running yet');
    }
}