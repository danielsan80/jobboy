<?php

namespace JobBoy\Process\Domain\Entity\Infrastructure\TouchCallback;

use JobBoy\Process\Domain\Entity\Process as BaseProcess;

class Process extends BaseProcess
{

    protected $callbacks = [];

    public function addTouchCallback(callable $callback)
    {
        $this->callbacks[] = $callback;
    }

    public function removeTouchCallback(callable $callback)
    {
        foreach ($this->callbacks as $i => $registeredCallback) {
            if ($registeredCallback === $callback) {
                unset($this->callbacks[$i]);
            }
        }
    }


    public function touch(): void
    {
        parent::touch();
        foreach ($this->callbacks as $callback) {
            $callback($this);
        }
    }

}