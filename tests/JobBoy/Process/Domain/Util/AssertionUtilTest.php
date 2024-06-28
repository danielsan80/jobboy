<?php

namespace Tests\JobBoy\Process\Domain\Util;

use PHPUnit\Framework\TestCase;

use JobBoy\Process\Domain\Util\AssertionUtil;

class AssertionUtilTest extends TestCase
{

    public static function scalarOrArrayOfScalarsProvider()
    {
        return [
            ['scalar_value', false],
            [[], false],
            [['key' => 'scalar_value'], false],
            [['scalar_value'], false],
            [['scalar_value',['key' => 'scalar_value']], false],

            [(new class(){}), true],
            [['key' => (new class(){})], true],
        ];

    }


    /**
     * @test
     * @dataProvider scalarOrArrayOfScalarsProvider
     */
    public function scalarOrArrayOfScalars($value, $thowException)
    {

        try {
            AssertionUtil::scalarOrArrayOfScalars($value);
            TestCase::assertFalse($thowException);
        } catch (\InvalidArgumentException $e) {
            TestCase::assertTrue($thowException);
        }

    }

}
