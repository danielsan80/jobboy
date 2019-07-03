<?php

namespace JobBoy\Process\Domain\Util;

use Assert\Assertion;

class AssertionUtil
{

    public static function scalarOrArrayOfScalars($value)
    {
        $value = [$value];
        array_walk_recursive($value, function ($item) {
            if (is_array($item)) {
                return;
            }

            Assertion::scalar($item);
        });
    }

}