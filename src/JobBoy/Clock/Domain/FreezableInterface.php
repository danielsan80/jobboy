<?php

namespace JobBoy\Clock\Domain;

interface FreezableInterface
{
    public function freeze($now): void;

    public function unfreeze(): void;
}