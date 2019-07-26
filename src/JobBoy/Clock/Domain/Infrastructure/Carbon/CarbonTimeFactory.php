<?php

namespace JobBoy\Clock\Domain\Infrastructure\Carbon;

use Carbon\Carbon;
use JobBoy\Clock\Domain\FreezableInterface;
use JobBoy\Clock\Domain\TimeFactoryInterface;

class CarbonTimeFactory implements TimeFactoryInterface, FreezableInterface
{

    /** @var \DateTime|null */
    protected $realFirstMicrotime;

    public function freeze($now): void
    {
        if (is_string($now)) {
            $now = new \DateTime($now);
        }
        Carbon::setTestNow($now);
        $this->realFirstMicrotime = null;
    }

    public function unfreeze(): void
    {
        Carbon::setTestNow();
        $this->realFirstMicrotime = null;
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

    public function microtime(): float
    {
        if (!Carbon::getTestNow()) {
            return (float)(new \DateTimeImmutable())->format('U.u');
        }

        if (!$this->realFirstMicrotime) {
            $this->realFirstMicrotime = (float)(new \DateTimeImmutable())->format('U.u');
            return (float)$this->createDateTimeImmutable()->format('U.u');
        }

        $realPastMicrotime = (float)(new \DateTimeImmutable())->format('U.u') - $this->realFirstMicrotime;

        return (float)$this->createDateTimeImmutable()->format('U.u') + $realPastMicrotime;
    }
}
