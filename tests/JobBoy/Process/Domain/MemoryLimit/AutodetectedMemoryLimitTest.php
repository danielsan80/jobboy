<?php

namespace Tests\JobBoy\Process\Domain\MemoryLimit;

use PHPUnit\Framework\TestCase;

use JobBoy\Process\Domain\MemoryLimit\AutodetectedMemoryLimit;

class AutodetectedMemoryLimitTest extends TestCase
{

    /**
     * @test
     */
    public function set_to_512MB_if_no_memory_limit()
    {
        $memoryLimit = new AutodetectedMemoryLimit(-1);
        $this->assertEquals('512M', AutodetectedMemoryLimit::MEMORY_LIMIT_DEFAULT);

        $expected = (int)((512 * 1024 * 1024) * AutodetectedMemoryLimit::MEMORY_LIMIT_MULTIPLIER);
        $this->assertEquals($expected, $memoryLimit->get());

    }

    /**
     * @test
     */
    public function memory_limit_in_GB()
    {
        $memoryLimit = new AutodetectedMemoryLimit('2G');
        $expected = (int)((2 * 1024 * 1024 * 1024) * AutodetectedMemoryLimit::MEMORY_LIMIT_MULTIPLIER);
        $this->assertEquals($expected, $memoryLimit->get());

    }

    /**
     * @test
     */
    public function memory_limit_in_MB()
    {
        $memoryLimit = new AutodetectedMemoryLimit('2M');
        $expected = (int)((2 * 1024 * 1024) * AutodetectedMemoryLimit::MEMORY_LIMIT_MULTIPLIER);
        $this->assertEquals($expected, $memoryLimit->get());

    }

    /**
     * @test
     */
    public function memory_limit_in_KB()
    {
        $memoryLimit = new AutodetectedMemoryLimit('2K');
        $expected = (int)((2 * 1024) * AutodetectedMemoryLimit::MEMORY_LIMIT_MULTIPLIER);
        $this->assertEquals($expected, $memoryLimit->get());

    }

}
