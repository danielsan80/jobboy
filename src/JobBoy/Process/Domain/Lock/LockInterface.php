<?php


namespace JobBoy\Process\Domain\Lock;


interface LockInterface
{

    public function acquire(): bool;
    public function release(): void;

}