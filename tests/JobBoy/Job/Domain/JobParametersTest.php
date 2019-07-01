<?php

namespace Tests\JobBoy\Job\Domain;

use PHPUnit\Framework\TestCase;

use JobBoy\Job\Domain\JobParameters;

class JobParametersTest extends TestCase
{

    /**
     * @test
     */
    public function create_and_extract()
    {
        $parameters = ['a_key' => 'a_value'];
        $jobParameters = new JobParameters($parameters);

        $this->assertSame($parameters, $jobParameters->all());
        $this->assertSame($parameters, $jobParameters->toScalar());

        $jobParameters = JobParameters::fromScalar($parameters);

        $this->assertSame($parameters, $jobParameters->all());

    }

    /**
     * @test
     */
    public function get()
    {
        $parameters = ['a_key' => 'a_value'];
        $jobParameters = new JobParameters($parameters);
        $this->assertSame('a_value', $jobParameters->get('a_key','a_default_value'));
        $this->assertSame('a_default_value', $jobParameters->get('a_not_existing_key', 'a_default_value'));

        $this->assertSame('a_value', $jobParameters['a_key']);
        $this->assertNull($jobParameters['a_not_existing_key']);

    }

    /**
     * @test
     */
    public function has()
    {
        $parameters = ['a_key' => 'a_value'];
        $jobParameters = new JobParameters($parameters);

        $this->assertTrue($jobParameters->has('a_key'));
        $this->assertFalse($jobParameters->has('a_not_existing_key'));

        $this->assertTrue(isset($jobParameters['a_key']));
        $this->assertFalse(isset($jobParameters['a_not_existing_key']));

    }

    /**
     * @test
     */
    public function set()
    {
        $parameters = ['a_key' => 'a_value'];
        $jobParameters = new JobParameters($parameters);

        $newJobParameters = $jobParameters->set('a_key', 'a_new_value');

        $this->assertSame('a_value', $jobParameters->get('a_key','a_default_value'));
        $this->assertSame('a_new_value', $newJobParameters->get('a_key','a_default_value'));

        try {
            $jobParameters['a_key'] = 'a_new_value';
            $this->fail();
        } catch (\LogicException $e) {
            $this->assertEquals('Method not supported. This is immutable', $e->getMessage());
        }
    }

    /**
     * @test
     */
    public function remove()
    {
        $parameters = ['a_key' => 'a_value'];
        $jobParameters = new JobParameters($parameters);

        $newJobParameters = $jobParameters->remove('a_key');

        $this->assertTrue($jobParameters->has('a_key'));
        $this->assertFalse($newJobParameters->has('a_key'));

        try {
            unset($jobParameters['a_key']);
            $this->fail();
        } catch (\LogicException $e) {
            $this->assertEquals('Method not supported. This is immutable', $e->getMessage());
        }
    }

    /**
     * @test
     */
    public function countable()
    {
        $parameters = ['a_key' => 'a_value'];
        $jobParameters = new JobParameters($parameters);

        $this->assertSame(1, $jobParameters->count());
        $this->assertCount(1, $jobParameters);

        $newJobParameters = $jobParameters->set('a_new_key', 'another_value');
        $this->assertSame(2, $newJobParameters->count());
        $this->assertCount(2, $newJobParameters);

    }

    /**
     * @test
     */
    public function equals()
    {
        $aParameters = ['a' => 'A', 'b' => 'B'];
        $bParameters = ['b' => 'B', 'a' => 'A'];

        $aJobParameters = new JobParameters($aParameters);
        $bJobParameters = new JobParameters($bParameters);

        $this->assertTrue($aJobParameters->equals($bJobParameters));
        $this->assertTrue($bJobParameters->equals($aJobParameters));

    }

    /**
     * @test
     */
    public function empty()
    {
        $jobParameters = JobParameters::empty();

        $this->assertSame([], $jobParameters->all());
    }

    /**
     * @test
     */
    public function keys()
    {
        $parameters = ['a' => 'A', 'b' => 'B'];

        $jobParameters = new JobParameters($parameters);

        $this->assertSame(['a', 'b'], $jobParameters->keys());
    }

    /**
     * @test
     */
    public function merge()
    {
        $aParameters = [
            'a' => 'A',
            'b' => 'B',
            'c' => [
                'c1' => 'C1',
                'c2' => 'C2',
            ]];
        $bParameters = [
            'b' => 'Beta',
            'c' => [
                'c1' => 'Gamma1',
            ],
            'd' => 'Delta'];

        $mergedParameters = [
            'a' => 'A',
            'b' => 'Beta',
            'c' => [
                'c1' => 'Gamma1',
            ],
            'd' => 'Delta'];
        $jobParameters = new JobParameters($aParameters);

        $newJobParameters = $jobParameters->merge($bParameters);

        $this->assertSame($aParameters, $jobParameters->all());
        $this->assertSame($mergedParameters, $newJobParameters->all());
    }

    /**
     * @test
     */
    public function iterator()
    {
        $parameters = [
            'a' => 'A',
            'b' => 'Beta',
            'c' => [
                'c1' => 'Gamma1',
            ]];
        $jobParameters = new JobParameters($parameters);

        $iterator = $jobParameters->getIterator();

        $iterator->rewind();
        $this->assertTrue($iterator->valid());
        $this->assertSame('a', $iterator->key());
        $this->assertSame('A', $iterator->current());

        $iterator->next();
        $this->assertTrue($iterator->valid());
        $this->assertSame('b', $iterator->key());
        $this->assertSame('Beta', $iterator->current());

        $iterator->next();
        $this->assertTrue($iterator->valid());
        $this->assertSame('c', $iterator->key());
        $this->assertSame(['c1' => 'Gamma1'], $iterator->current());

        $iterator->next();
        $this->assertFalse($iterator->valid());

        $i=0;
        foreach ($jobParameters as $key => $value) {
            $i++;
        }
        $this->assertSame(3, $i);

    }

    /**
     * @test
     */
    public function parameters_keys_should_not_have_strang_chars()
    {
        $parameters = ['[strange_key]' => 'a_value'];

        try {
            new JobParameters($parameters);
            $this->fail();
        } catch (\InvalidArgumentException $e) {
            $this->assertEquals('Value "[strange_key]" contains not allowed characters: "[", "]"', $e->getMessage());
        }

    }

}
