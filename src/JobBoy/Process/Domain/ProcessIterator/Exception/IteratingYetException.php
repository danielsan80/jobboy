<?php

namespace JobBoy\Process\Domain\ProcessIterator\Exception;

class IteratingYetException extends \RuntimeException
{
    public function __construct()
    {
        parent::__construct('I\'m iterating yet');
    }

}