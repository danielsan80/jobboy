<?php

namespace JobBoy\Process\Domain\IterationMaker\Exception;

class IteratingYetException extends \DomainException
{
    public function __construct()
    {
        parent::__construct('I\'m iterating yet');
    }

}