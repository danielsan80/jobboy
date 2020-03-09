<?php

namespace JobBoy\Process\Domain\Entity\Infrastructure\Hydratable\Traits;

use Assert\Assertion;
use JobBoy\Process\Domain\Entity\Infrastructure\Hydratable\Data\HydratableProcessData;

trait HydrateMethod
{

    public function hydrate(HydratableProcessData $data): void
    {
        Assertion::allNotNull([
            $data->status(),
            $data->createdAt(),
            $data->updatedAt(),
            $data->store(),
        ]);

        if ($data->status()->isStarting()) {
            Assertion::null($data->startedAt());
        }

        if ($data->status()->isActive()) {
            Assertion::null($data->endedAt());
        }

        if (!$data->status()->isActive()) {
            Assertion::notNull($data->endedAt());
        }

        if ($data->killedAt()) {
            Assertion::true($data->status()->isFailed() || $data->status()->isFailing());
        }

        $this->status = $data->status();
        $this->createdAt = $data->createdAt();
        $this->updatedAt = $data->updatedAt();
        $this->startedAt = $data->startedAt();
        $this->endedAt = $data->endedAt();
        $this->handledAt = $data->handledAt();
        $this->killedAt = $data->killedAt();
        $this->store = $data->store();
    }

}