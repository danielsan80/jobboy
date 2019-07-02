<?php

namespace JobBoy\Process\Domain\Entity\Data;

use JobBoy\Process\Domain\Entity\Id\ProcessId;
use JobBoy\Process\Domain\ProcessParameters;
use JobBoy\Process\Domain\ProcessStatus;

class CreateProcessData
{

    /** @var ProcessId */
    protected $id;

    /** @var string */
    protected $code;

    /** @var ProcessParameters */
    protected $parameters;

    public function __construct(array $data = [])
    {
        foreach ($data as $key => $value) {
            $setter = 'set' . ucfirst($key);
            $this->$setter($value);
        }
    }

    public function setId(?ProcessId $id): self
    {
        $this->id = $id;
        return $this;
    }

    public function setCode(?string $code): self
    {
        $this->code = $code;
        return $this;
    }

    public function setParameters(?ProcessParameters $parameters): self
    {
        $this->parameters = $parameters;
        return $this;
    }


    public function id(): ?ProcessId
    {
        return $this->id;
    }

    public function code(): ?string
    {
        return $this->code;
    }

    public function parameters(): ?ProcessParameters
    {
        return $this->parameters;
    }

}
