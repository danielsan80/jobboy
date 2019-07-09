<?php

namespace Dan\Clock\Domain;

interface FreezableInterface
{
    public function freeze($now): void;

    public function unfreeze(): void;
}