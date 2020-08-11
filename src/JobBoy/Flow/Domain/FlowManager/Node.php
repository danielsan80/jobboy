<?php

namespace JobBoy\Flow\Domain\FlowManager;

use Assert\Assertion;

final class Node
{
    protected $job;
    protected $code;

    private function __construct(string $job, string $code)
    {
        Assertion::notBlank($job);
        Assertion::notBlank($code);
        $this->job = $job;
        $this->code = $code;
    }

    public static function fromArray(array $data): self
    {
        $defaults = [
            'job' => null,
            'code' => null,
        ];

        foreach ($data as $key => $value) {
            Assertion::keyExists($defaults, $key, sprintf('"%s" is not a valid key: allowed keys are [%s]', $key, implode(', ', array_keys($defaults))));
        }
        $data = array_merge($defaults, $data);

        return new self($data['job'], $data['code']);
    }

    public static function create(string $job, string $code): self
    {
        return new self($job, $code);
    }

    public function job(): string
    {
        return $this->job;
    }

    public function code(): string
    {
        return $this->code;
    }

    public function __toString()
    {
        return $this->job.'.'.$this->code;
    }

}