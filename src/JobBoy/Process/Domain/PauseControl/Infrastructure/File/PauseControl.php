<?php

namespace JobBoy\Process\Domain\PauseControl\Infrastructure\File;

use JobBoy\Process\Domain\PauseControl\PauseControl as PauseControlInterface;

class PauseControl implements PauseControlInterface
{
    const DEFAULT_FILE = '/jobboy/pause_control';

    protected $filename;

    public function __construct(?string $filename=null)
    {
        if (!$this->filename) {
            $filename = sys_get_temp_dir() . self::DEFAULT_FILE;
        }

        $this->filename = $filename;
    }


    protected function ensureFileExists(): void
    {
        $dir = dirname($this->filename);

        if (!file_exists($dir)) {
            mkdir($dir, 0755, true);
        }

        if (!file_exists($this->filename)) {
            touch($this->filename);
        }
    }

    public function pause(): void
    {
        file_put_contents($this->filename,'true');
    }

    public function unpause(): void
    {
        file_put_contents($this->filename,'false');
    }

    public function isPaused(): bool
    {
        $paused = file_get_contents($this->filename);
        return $paused=='true';
    }
}