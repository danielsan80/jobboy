<?php

namespace Dan\Clock\Domain\Infrastructure\Carbon;

use Carbon\Carbon;
use Dan\Clock\Domain\FreezableInterface;
use Dan\Clock\Domain\TimeFactoryInterface;

class CarbonTimeFactory implements TimeFactoryInterface, FreezableInterface
{

    public function freeze($now): void
    {
        if (is_string($now)) {
            $now = new \DateTime($now);
        }
        Carbon::setTestNow($now);
    }

    public function unfreeze(): void
    {
        Carbon::setTestNow();
    }

    public function createDateTimeImmutable(string $time = "now", ?\DateTimeZone $timezone = NULL): \DateTimeImmutable
    {
        $carbon = new Carbon($time, $timezone);
        $datetime = \DateTimeImmutable::createFromMutable($carbon);
        return $datetime;
    }

    public function createDateTime(string $time = "now", ?\DateTimeZone $timezone = NULL): \DateTime
    {
        $carbon = new Carbon($time, $timezone);
        $datetime = new \DateTime(null, $carbon->getTimezone());
        $datetime->setTimestamp($carbon->getTimestamp());
        return $datetime;
    }

}
