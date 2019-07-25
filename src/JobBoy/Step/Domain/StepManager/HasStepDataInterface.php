<?php

namespace JobBoy\Step\Domain\StepManager;

interface HasStepDataInterface
{
    public function stepData(): StepData;
}