<?php

namespace JobBoy\Process\Domain\Lock;

interface LockFactoryInterface
{

    public function create(string $name): LockInterface;

}