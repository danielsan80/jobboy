<?php
declare(strict_types=1);

namespace JobBoy\Flow\Domain\FlowManager\JobSchema\Model\Job;

class Job
{
    /** @var JobCode */
    private $code;

    /** @var string */
    private $name;

    private function __construct()
    {
    }

    public static function create(JobCode $code, string $name): self
    {
        $job = new self();

        $job->code = $code;
        $job->name = $name;

        return $job;
    }

    public static function fromString(string $code, ?string $name = null): self
    {
        $code = JobCode::create($code);
        $name = $name ?? (string)$code;

        return self::create($code, $name);
    }

    public function code(): JobCode
    {
        return $this->code;
    }

    public function name(): string
    {
        return $this->name;
    }

    public function __toString(): string
    {
        return (string)$this->code;
    }

}
