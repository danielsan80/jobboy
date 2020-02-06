<?php

namespace JobBoy\Process\Application\Service;


use JobBoy\Process\Domain\KillList\KillList;

class KillProcess
{

    /** @var KillList */
    protected $killList;

    public function __construct(
        KillList $killList
    )
    {
        $this->killList = $killList;
    }

    public function execute(string $processId): void
    {
        if ($this->killList->inList($processId)) {
            return;
        }

        $this->killList->add($processId);
    }

}