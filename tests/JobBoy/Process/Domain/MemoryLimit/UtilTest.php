<?php

namespace Tests\JobBoy\Process\Domain\MemoryLimit;

use PHPUnit\Framework\TestCase;

use JobBoy\Process\Domain\MemoryControl\Util;

class UtilTest extends TestCase
{

    public function test_bytesToString()
    {
        $this->assertEquals('1023B', Util::bytesToString(1023));
        $this->assertEquals('1K', Util::bytesToString(1024));
        $this->assertEquals('1024K', Util::bytesToString(pow(1024,2)-1));
        $this->assertEquals('1M', Util::bytesToString(pow(1024,2)));
        $this->assertEquals('1024M', Util::bytesToString(pow(1024,3)-1));
        $this->assertEquals('1G', Util::bytesToString(pow(1024,3)));
        $this->assertEquals('1024G', Util::bytesToString(pow(1024,4)-1));
        $this->assertEquals('1T', Util::bytesToString(pow(1024,4)));
        $this->assertEquals('1024T', Util::bytesToString(pow(1024,5)-1));
        $this->assertEquals(pow(1024,5).'B', Util::bytesToString(pow(1024,5)));




    }
}
