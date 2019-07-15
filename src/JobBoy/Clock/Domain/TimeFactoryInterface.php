<?php


namespace JobBoy\Clock\Domain;


interface TimeFactoryInterface
{

    public function createDateTimeImmutable(string $time = "now", ?\DateTimeZone $timezone = NULL): \DateTimeImmutable;

    public function createDateTime(string $time = "now", ?\DateTimeZone $timezone = NULL): \DateTime;
}