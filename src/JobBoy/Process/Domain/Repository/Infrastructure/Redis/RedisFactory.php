<?php

namespace JobBoy\Process\Domain\Repository\Infrastructure\Redis;

class RedisFactory
{
    /** @var string */
    protected $host;
    /** @var string */
    protected $port;

    public function __construct(string $host, string $port = null)
    {
        $this->host = $host;
        $this->port = $port;
    }

    public function create(): \Redis
    {
        $args = [$this->host];

        if ($this->port !== null) {
            $args[] = $this->port;
        }

        $client = new \Redis();
        $client->connect(...$args);
        $client->setOption(\Redis::OPT_SERIALIZER, \Redis::SERIALIZER_PHP);

        return $client;
    }

}