<?php

namespace JobBoy\Job\Domain;

use Assert\Assertion;
use JobBoy\Job\Domain\Util\AssertionUtil;

/**
 * @immutable
 */
class JobParameters implements \IteratorAggregate, \Countable, \ArrayAccess
{
    /** @var array */
    protected $parameters;

    /**
     * @param array $parameters An array of parameters
     */
    public function __construct(array $parameters = [])
    {
        $this->setParameters($parameters);
    }

    protected function setParameters(array $parameters): void
    {
        foreach ($parameters as $key => $value) {
            Assertion::string($key);
            Assertion::notRegex($key, '/[\[\]]+/','Value "%s" contains not allowed characters: "[", "]"');
            AssertionUtil::scalarOrArrayOfScalars($value);
        }

        $this->parameters = $parameters;
    }

    public static function fromScalar(array $parameters): self
    {
        return new static($parameters);
    }

    public function toScalar()
    {
        return $this->all();
    }

    public function equals(self $jobParameters): bool
    {
        $aParameters = $this->parameters;
        $bParameters = $jobParameters->all();
        ksort($aParameters);
        ksort($bParameters);

        return ($aParameters==$bParameters);
    }

    public static function empty(): self
    {
        return new static([]);
    }

    /**
     * Returns the parameters.
     *
     * @return array An array of parameters
     */
    public function all(): array
    {
        return $this->parameters;
    }

    /**
     * Returns the parameter keys.
     *
     * @return array An array of parameter keys
     */
    public function keys(): array
    {
        return array_keys($this->parameters);
    }

    /**
     * Merge with othern parameters
     *
     * @param array|JobParameters $parameters An array of parameters or a JobParameters
     * @return self
     */
    public function merge($parameters = []): self
    {
        if ($parameters instanceof self) {
            $parameters = $parameters->toScalar();
        }
        return $this->doMerge($parameters);
    }

    /**
     * Adds parameters.
     *
     * @param array $parameters An array of parameters
     * @return self
     */
    protected function doMerge(array $parameters = []): self
    {
        return new static(array_replace($this->parameters, $parameters));
    }

    /**
     * Returns an iterator for parameters.
     *
     * @return \ArrayIterator An \ArrayIterator instance
     */
    public function getIterator()
    {
        return new \ArrayIterator($this->parameters);
    }

    /**
     * Returns the number of parameters.
     *
     * @return int The number of parameters
     */
    public function count(): int
    {
        return count($this->parameters);
    }


    /**
     * Returns true if the parameter is defined.
     *
     * @param string $key The key
     *
     * @return bool true if the parameter exists, false otherwise
     */
    public function has($key): bool
    {
        return array_key_exists($key, $this->parameters);
    }

    /**
     * Returns a parameter by name.
     *
     * @param string $key The key
     * @param mixed $default The default value if the parameter key does not exist
     *
     * @return mixed
     */
    public function get($key, $default = null)
    {
        return array_key_exists($key, $this->parameters) ? $this->parameters[$key] : $default;
    }

    /**
     * Sets a parameter by name.
     *
     * @param string $key The key
     * @param mixed $value The value
     *
     * @return self
     */
    public function set($key, $value): self
    {
        $parameters = $this->parameters;
        $parameters[$key] = $value;
        return new static($parameters);
    }

    /**
     * Removes a parameter.
     *
     * @param string $key The key
     * @return self
     */
    public function remove($key): self
    {
        $parameters = $this->parameters;
        unset($parameters[$key]);

        return new static($parameters);
    }

    /**
     * Required by interface ArrayAccess.
     *
     * {@inheritDoc}
     */
    public function offsetExists($offset)
    {
        return $this->has($offset);
    }

    /**
     * Required by interface ArrayAccess.
     *
     * {@inheritDoc}
     */
    public function offsetGet($offset)
    {
        return $this->get($offset);
    }



    /**
     * Required by interface ArrayAccess.
     *
     * {@inheritDoc}
     */
    public function offsetSet($offset, $value)
    {
        throw new \LogicException('Method not supported. This is immutable');
    }

    /**
     * Required by interface ArrayAccess.
     *
     * {@inheritDoc}
     */
    public function offsetUnset($offset)
    {
        throw new \LogicException('Method not supported. This is immutable');
    }

}