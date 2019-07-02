<?php


namespace Dan\Clock\Domain;


interface TimeFactoryInterface
{
    public function freeze($now): void;

    public function unfreeze(): void;

    public function createDateTimeImmutable(string $time = "now", ?\DateTimeZone $timezone = NULL): \DateTimeImmutable;

    public function createDateTime(string $time = "now", ?\DateTimeZone $timezone = NULL): \DateTime;
}