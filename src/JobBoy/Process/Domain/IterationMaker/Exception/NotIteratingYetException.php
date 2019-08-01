<?php

namespace JobBoy\Process\Domain\IterationMaker\Exception;

class NotIteratingYetException extends \DomainException
{
    public function __construct()
    {
        parent::__construct('I\'m not iterating yet');
    }

}