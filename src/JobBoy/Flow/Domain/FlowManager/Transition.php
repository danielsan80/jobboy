<?php

namespace JobBoy\Flow\Domain\FlowManager;

use Assert\Assertion;

class Transition
{
    protected $from;
    protected $to;
    protected $on;

    private function __construct(?Node $from, ?Node $to, ?string $on)
    {
        if (!$from) {
            Assertion::notNull($to);
        }
        if (!$to) {
            Assertion::notNull($from);
        }
        if (!$on) {
            Assertion::null($from);
            Assertion::notNull($to);
        }
        if ($on) {
            Assertion::notNull($from);
        }

        if ($from && $to) {
            Assertion::eq($from->job(), $to->job());
            Assertion::notEq($from->code(), $to->code());
        }

        $this->from = $from;
        $this->to = $to;
        $this->on = $on;
    }

    public static function create(array $data): self
    {
        $defaults = [
            'from' => null,
            'to' => null,
            'on' => null,
        ];

        foreach ($data as $key => $value) {
            Assertion::keyExists($defaults, $key, sprintf('"%s" is not a valid key: allowed keys are [%s]', $key, implode(', ', array_keys($defaults))));
        }
        $data = array_merge($defaults, $data);

        return new self($data['from'], $data['to'], $data['on']);
    }

    public static function createNodeChange(Node $from, Node $to, string $on): self
    {
        return self::create([
            'from' => $from,
            'to' => $to,
            'on' => $on,
        ]);
    }


    public static function createEntry(Node $node): self
    {
        return self::create([
            'to' => $node,
        ]);
    }

    public static function createExit(Node $node, string $on): self
    {
        return self::create([
            'from' => $node,
            'on' => $on,
        ]);
    }

    public function from(): ?Node
    {
        return $this->from;
    }

    public function to(): ?Node
    {
        return $this->to;
    }

    public function on(): ?string
    {
        return $this->on;
    }

    public function isEntry(): bool
    {
        return !$this->from;
    }

    public function isExit(): bool
    {
        return !$this->to;
    }

    public function isNodeChange(): bool
    {
        return $this->from && $this->to;
    }

    public function job(): string
    {
        return $this->from ? $this->from->job() : $this->to->job();
    }

    public function __toString()
    {
        $from = $this->from ? $this->from->code() : '⚫';
        $to = $this->to ? $this->to->code() : '⚪';
        return $this->job() . ':' . $from . '-' . $this->on . '->' . $to;
    }

}