<?php

namespace JobBoy\Process\Domain;

use Assert\Assertion;
use JobBoy\Process\Domain\Util\AssertionUtil;

/**
 * @immutable
 */
class ProcessStore
{
    /** @var array */
    protected $data;

    public function __construct(array $data = [])
    {
        foreach ($data as $key => $value) {
            Assertion::string($key);
            AssertionUtil::scalarOrArrayOfScalars($value);
//            Assertion::notRegex($key, '/[\[\]]+/','Value "%s" contains not allowed characters: "[", "]"');
        }

        $this->data = $data;
    }

    public function prepend(array $data): self
    {
        return new self(array_replace($data, $this->data));
    }

    public function append(array $data): self
    {
        return new self(array_replace($this->data, $data));
    }


    public function unset(string $key): self
    {
        $data = $this->data;
        unset($data[$key]);
        return new self($data);
    }

    public function has(string $key): bool
    {
        return array_key_exists($key, $this->data);
    }

    public function get(string $key, $default = null)
    {
        if ($this->has($key)) {
            return $this->data[$key];
        }
        return $default;
    }

    public function set(string $key, $value): self
    {
        $data = $this->data;
        $data[$key] = $value;
        return new self($data);
    }

    public function equals(ProcessStore $store): bool
    {
        return $this->data == $store->data();
    }

    public function data(): array
    {
        return $this->data;
    }

}