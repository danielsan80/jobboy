<?php

namespace JobBoy\Clock\Domain;

final class Clock
{
    private static $timeFactory;

    public static function setTimeFactory(TimeFactoryInterface $timeFactory)
    {
        self::$timeFactory = $timeFactory;
    }

    public static function resetTimeFactory()
    {
        self::$timeFactory = null;

    }

    public static function createDateTimeImmutable(string $time = "now", ?\DateTimeZone $timezone = NULL): \DateTimeImmutable
    {
        self::ensureTimeFactoryExists();
        return self::$timeFactory->createDateTimeImmutable($time, $timezone);
    }

    public static function createDateTime(string $time = "now", ?\DateTimeZone $timezone = NULL): \DateTime
    {
        self::ensureTimeFactoryExists();
        return self::$timeFactory->createDateTime($time, $timezone);
    }

    private static function ensureTimeFactoryExists()
    {
        if (!self::$timeFactory) {
            self::$timeFactory = new DefaultTimeFactory();
        }
    }

}