<?php

namespace Tests\Redis;

use PHPUnit\Framework\TestCase;


class RedisTest extends TestCase
{

    /**
     * @test
     */
    public function how_it_works()
    {

        $redis = new \Redis();
        $redis->connect('redis');
        $redis->setOption(\Redis::OPT_SERIALIZER, \Redis::SERIALIZER_PHP);

        $redis->flushDB();

        $this->assertEquals(false, $redis->get('key'));
        $redis->set('key', 'value');

        $this->assertEquals('value', $redis->get('key'));


        $this->assertEquals([
            'key'
        ], $redis->keys('*'));


        $redis->delete($redis->keys('*'));

        $this->assertEquals([], $redis->keys('*'));


        $redis->flushDB();
        $this->assertEquals(false, $redis->get('key'));

        $redis->hSet('test', 'key1', 'value1');
        $redis->hSet('test', 'key2', 'value2');

        $this->assertEquals(['test'], $redis->keys('*'));
        $this->assertEquals(false, $redis->get('test'));
        $this->assertEquals(['key1', 'key2'], $redis->hKeys('test'));
        $this->assertEquals('value1', $redis->hGet('test', 'key1'));
        $this->assertEquals([
            'key1' => 'value1',
            'key2' => 'value2',
        ], $redis->hGetAll('test'));

        $this->assertEquals(2, $redis->hLen('test'));

        $redis->delete('test');

        $this->assertEquals([
        ], $redis->hGetAll('test'));


    }

}
