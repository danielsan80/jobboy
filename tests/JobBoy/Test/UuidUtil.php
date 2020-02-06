<?php

namespace Tests\JobBoy\Test;

class UuidUtil
{
    static public function uuid(int $i): string
    {
        return '00000000-0000-4000-8000-'.str_pad($i,12,'0',STR_PAD_LEFT);
    }

}