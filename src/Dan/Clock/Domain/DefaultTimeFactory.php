<?php

namespace Dan\Clock\Domain;

class DefaultTimeFactory implements TimeFactoryInterface
{


    public function createDateTimeImmutable(string $time = "now", ?\DateTimeZone $timezone = NULL): \DateTimeImmutable
    {
        return new \DateTimeImmutable($time, $timezone);
    }

    public function createDateTime(string $time = "now", ?\DateTimeZone $timezone = NULL): \DateTime
    {
        return new \DateTime($time, $timezone);
    }

}