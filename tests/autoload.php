<?php

use Symfony\Component\Dotenv\Dotenv;

require __DIR__.'/../vendor/autoload.php';

//if (!isset($_SERVER['APP_ENV'])) {
    if (!class_exists(Dotenv::class)) {
        throw new \RuntimeException('APP_ENV environment variable is not defined. You need to define environment variables for configuration or add "symfony/dotenv" as a Composer dependency to load variables from a .env file.');
    }
    $dotenv = new Dotenv(true);
    $dotenv->load(__DIR__.'/../.env');

    if (file_exists(__DIR__.'/../.env.test')) {
        $dotenv->overload(__DIR__.'/../.env.test');
    }
//}