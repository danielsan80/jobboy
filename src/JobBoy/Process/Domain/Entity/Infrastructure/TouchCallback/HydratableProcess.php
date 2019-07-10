<?php

namespace JobBoy\Process\Domain\Entity\Infrastructure\TouchCallback;


use JobBoy\Process\Domain\Entity\Infrastructure\Hydratable\Traits\HydrateMethod;

class HydratableProcess extends Process
{
    use HydrateMethod;
}