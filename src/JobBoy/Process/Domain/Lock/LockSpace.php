<?php

namespace JobBoy\Process\Domain\Lock;

class LockSpace
{
    const DEFUALT = 'default';

    /** @var string */
    protected $space;

    /** @var string|null */
    protected $hash;


    public function __construct(?string $space = null)
    {
        if (!$space) {
            $space = self::DEFUALT;
        }
        $this->space = $space;
    }

    public function hash(): string
    {
        if ($this->hash) {
            return $this->hash;
        }
        $this->hash = sha1($this->space);

        return $this->hash;
    }

    public function __toString()
    {
        return $this->hash();
    }

}