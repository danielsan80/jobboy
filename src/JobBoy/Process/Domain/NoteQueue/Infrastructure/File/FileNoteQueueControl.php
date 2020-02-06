<?php

namespace JobBoy\Process\Domain\NoteQueue\Infrastructure\File;

use JobBoy\Process\Domain\Lock\LockFactoryInterface;
use JobBoy\Process\Domain\Lock\LockInterface;
use JobBoy\Process\Domain\NoteQueue\NoteQueue;
use JobBoy\Process\Domain\NoteQueue\NoteQueueControl;

class FileNoteQueueControl implements NoteQueueControl
{

    const LOCK_KEY = 'work-control';
    const DEFAULT_FILE = '/jobboy/work-control';

    protected $filename;

    /** @var LockFactoryInterface */
    protected $lockFactory;

    /** @var LockInterface */
    protected $lock;

    /** @var NoteQueue */
    protected $queue;

    public function __construct(LockFactoryInterface $lockFactory, ?string $filename=null)
    {
        if (!$filename) {
            $filename = sys_get_temp_dir() . self::DEFAULT_FILE;
        }

        $this->lockFactory = $lockFactory;
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

    protected function lock(): void
    {
        if ($this->lock) {
            throw new \LogicException('You cannot lock more then one time without release');
        }
        $this->lock = $this->lockFactory->create(self::LOCK_KEY);
        $attempts = 20;
        while (!$this->lock->acquire()) {

            if ($attempts--<0) {
                throw new \LogicException('I cannot acquire lock on command queue');
            }
            usleep(50000);
        };
    }

    protected function release(): void
    {
        if (!$this->lock) {
            throw new \LogicException('You can release only after have locked');
        }
        $this->lock->release();
        $this->lock = null;
    }

    protected function loadQueue(): void
    {
        $records = json_decode(file_get_contents($this->filename));
        if (!$records) {
            $records = [];
        }
        $commands = [];
        foreach ($records as $record) {
            $commands[] = unserialize($record);
        }
        $this->queue = new NoteQueue($commands);
    }

    protected function saveQueue(): void
    {
        $records = [];
        foreach ($this->queue->all() as $command) {
            $records[] = serialize($command);
        }
        file_put_contents($this->filename, json_encode($records,true));
    }


    protected function begin(): void
    {
        $this->lock();
        $this->loadQueue();
    }

    protected function commit(): void
    {
        $this->saveQueue();
        $this->release();
    }

    public function push($note): void
    {
        $this->ensureFileExists();
        $this->begin();
        $this->queue->add($note);
        $this->commit();
    }

    public function get(): array
    {
        $this->ensureFileExists();
        $this->loadQueue();
        return $this->queue->all();
    }

    public function resolve(callable $resolver)
    {
        $this->ensureFileExists();
        $this->begin();
        $resolver($this->queue);
        $this->commit();
    }

}