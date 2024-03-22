<?php
declare(strict_types=1);

namespace JobBoy\Flow\Domain\FlowManager\StateDiagram\Builder;

use Assert\Assertion;
use JobBoy\Flow\Domain\FlowManager\StateDiagram\Model\Job\Job;
use JobBoy\Flow\Domain\FlowManager\StateDiagram\Model\State\State;
use JobBoy\Flow\Domain\FlowManager\StateDiagram\StateDiagram;

class StateDiagramBuilder
{
    /** @var Job|null */
    private $job = null;

    /** @var array<string,State> */
    private $states = [];

    private function __construct()
    {
    }

    public static function create(): self
    {
        return new self();
    }

    public function createJob(string $code): self
    {
        $clone = clone $this;
        $clone->job = Job::create($code);

        return $clone;
    }

    public function createState(string $code): self
    {
        $clone = clone $this;
        $clone->states[$code] = State::create($code);
        return $clone;
    }

    public function setStateParent(string $code, string $parentCode): self
    {
        Assertion::keyExists($this->states, $code, sprintf('State %s not found', $code));
        Assertion::keyExists($this->states, $parentCode, sprintf('State %s not found', $parentCode));

        $clone = clone $this;
        $clone->states[$code] = $clone->states[$code]->setParent($clone->states[$parentCode]);
        return $clone;
    }

    public function build(): StateDiagram
    {
        return new StateDiagram();
    }

}
