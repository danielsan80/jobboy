<?php

namespace Tests\Dan\Clock\Test\Fixtures;

use Dan\Clock\Domain\Clock;
use Dan\Clock\Domain\Infrastructure\Carbon\CarbonTimeFactory;
use Dan\FixtureHandler\Fixture\AbstractFixture;

class ClockFixture extends AbstractFixture
{

    public function load(): void
    {
        $dateTimeFactory = new CarbonTimeFactory();
        Clock::setDateTimeFactory($dateTimeFactory);

        $this->setRef('clock', $dateTimeFactory);

    }
}