<?php

return [
    'configs' => [
        'debug' => true,
        'log' => true,
        'name' => 'app',
        'version' => '1.0',
        'charset' => 'utf-8',
        'timezone' => 'Asia/Shanghai',
        'cache.path' => __DIR__ . '/storage/cache',
        'log.path' => __DIR__ . '/storage/logs',
        'db' => [
            'database_type' => 'mysql',
            'database_name' => 'demo',
            'server' => '127.0.0.1',
            'username' => 'root',
            'password' => '',
            'charset' => 'utf-8',
            'prefix' => 'syan_',
        ],
        'theme' => 'default',
        'roles' => [
            'admin' => 0,
            'user' => 1,
        ],
        'key' => 'hjJGHSYkHHHG98GHGWN',
    ],
];