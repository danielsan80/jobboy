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

    public static function create(array $data): self
    {
        $defaults = [
            'from' => null,
            'to' => null,
            'on' => null,
        ];
        $data = array_merge($defaults, $data);
        return new self($data['from'], $data['to'], $data['on']);
    }

    public static function createNodeChange(string $from, string $to, string $on): self
    {
        return self::create([
            'from' => $from,
            'to' => $to,
            'on' => $on,
        ]);
    }


    public static function createEntry(string $node): self
    {
        return self::create([
            'to' => $node,
        ]);
    }

    public static function createExit(string $node, string $on): self
    {
        return self::create([
            'from' => $node,
            'on' => $on,
        ]);
    }

    public function toTransaction(Job $job): RealTransition
    {
        return RealTransition::create([
            'from' => $this->realTransition->from()?Node::create($job->code(), $this->realTransition->from()->code()):null,
            'to' => $this->realTransition->to()?Node::create($job->code(), $this->realTransition->to()->code()):null,
            'on' => $this->realTransition->on(),
        ]);
    }
}
