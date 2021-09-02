<?php
declare(strict_types=1);

namespace JobBoy\Flow\Domain\FlowManager\TransitionLoader;

use JobBoy\Flow\Domain\FlowManager\Node;
use JobBoy\Flow\Domain\FlowManager\Transition as RealTransition;

class Transition
{
    private const JOB = 'job';
    private $realTransition;

    private function __construct(?string $from, ?string $to, ?string $on)
    {
        $this->realTransition = RealTransition::create([
            'from' => $from ? Node::create(self::JOB, $from) : null,
            'to' => $to ? Node::create(self::JOB, $to) : null,
            'on' => $on,
        ]);
    }

    public static function fromArray(array $data): self
    {
        $defaults = [
            'from' => null,
            'to' => null,
            'on' => null,
        ];
        $data = array_merge($defaults, $data);
        return new self($data['from'], $data['to'], $data['on']);
    }

    public static function nodeChange(string $from, string $to, string $on): self
    {
        return self::fromArray([
            'from' => $from,
            'to' => $to,
            'on' => $on,
        ]);
    }


    public static function entry(string $node): self
    {
        return self::fromArray([
            'to' => $node,
        ]);
    }

    public static function exit(string $node, string $on): self
    {
        return self::fromArray([
            'from' => $node,
            'on' => $on,
        ]);
    }

    public function toTransaction(string $job): RealTransition
    {
        return RealTransition::create([
            'from' => $this->realTransition->from()?Node::create($job, $this->realTransition->from()->code()):null,
            'to' => $this->realTransition->to()?Node::create($job, $this->realTransition->to()->code()):null,
            'on' => $this->realTransition->on(),
        ]);
    }
}
