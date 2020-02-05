<?php

namespace JobBoy\Process\Domain\NoteQueue;

class NoteQueue
{

    /** @var array */
    protected $notes;

    public function __construct(array $notes = [])
    {
        $this->notes = $notes;
    }

    public function add($note)
    {
        $this->notes[] = $note;
    }

    public function remove($note)
    {
        foreach ($this->notes as $i => $currentNote) {
            if ($currentNote===$note) {
                unset($this->notes[$i]);
            }
        }
    }

    public function all(): array
    {
        return $this->notes;
    }

}