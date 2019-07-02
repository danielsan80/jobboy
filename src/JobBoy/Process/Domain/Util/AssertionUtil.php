<?php

namespace JobBoy\Process\Domain\Util;

use Assert\Assertion;

class AssertionUtil
{

    public static function scalarOrArrayOfScalars($value)
    {
        $stack = [$value];
        while ($stack) {
            $el = array_shift($stack);
            if (is_array($el)) {
                foreach ($el as $subEl) {
                    $stack[] = $subEl;
                }
                continue;
            }
            Assertion::scalar($el);
        }
    }

}