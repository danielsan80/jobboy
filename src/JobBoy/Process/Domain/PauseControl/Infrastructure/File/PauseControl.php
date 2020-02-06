<?php

namespace JobBoy\Process\Domain\PauseControl\Infrastructure\File;

use JobBoy\Process\Domain\PauseControl\PauseControl as PauseControlInterface;

/**
 * @deprecated use JobBoy\Process\Domain\PauseControl\Infrastructure\NoteQueue\PauseControl
 */
class PauseControl implements PauseControlInterface
{
    const DEFAULT_FILE = '/jobboy/pause_control';

    protected $filename;

    public function __construct(?string $filename=null)
    {
        if (!$filename) {
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
        $this->ensureFileExists();

        file_put_contents($this->filename,'true');
    }

    public function unpause(): void
    {
        $this->ensureFileExists();

        file_put_contents($this->filename,'false');
    }

    public function isPaused(): bool
    {
        $this->ensureFileExists();

        $paused = file_get_contents($this->filename);
        return $paused=='true';
    }

    public function resolveRequests(): void
    {
    }
}