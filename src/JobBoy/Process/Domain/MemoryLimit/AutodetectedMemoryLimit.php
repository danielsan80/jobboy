<?php

namespace JobBoy\Process\Domain\MemoryLimit;

class AutodetectedMemoryLimit implements MemoryLimit
{
    const MEMORY_LIMIT_MULTIPLIER = 0.8;
    const MEMORY_LIMIT_DEFAULT = '512M';
    /** @var null|string */
    protected $overrideMemoryLimit;
    /** @var int */
    private $memoryLimit;

    public function __construct(?string $overrideMemoryLimit = null)
    {
        $this->overrideMemoryLimit = $overrideMemoryLimit;
    }

    protected function ensureMemoryLimitWasCalculated(): void
    {
        if ($this->memoryLimit) {
            return;
        }

        if (is_null($this->overrideMemoryLimit)) {
            $memoryLimit = ini_get('memory_limit');
        } else {
            $memoryLimit = $this->overrideMemoryLimit;
        }

        if ($memoryLimit == -1) {
            $memoryLimit = self::MEMORY_LIMIT_DEFAULT;
        }

        if (preg_match('/^(?P<num>\d+)(?P<unit>.)$/', $memoryLimit, $matches)) {
            if ($matches['unit'] == 'G') {
                $memoryLimit = $matches['num'] * 1024 * 1024 * 1024;
            } else if ($matches['unit'] == 'M') {
                $memoryLimit = $matches['num'] * 1024 * 1024;
            } else if ($matches['unit'] == 'K') {
                $memoryLimit = $matches['num'] * 1024;
            }
        }

        $memoryLimit *= self::MEMORY_LIMIT_MULTIPLIER;

        $this->memoryLimit = (int)$memoryLimit;
    }

    public function get(): int
    {
        $this->ensureMemoryLimitWasCalculated();
        return $this->memoryLimit;
    }

    public function isExceeded(): bool
    {
        return Util::isMemoryLimitExceeded($this->get());
    }


}