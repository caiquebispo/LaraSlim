<?php

return [
    'app' => [
        'name' => 'LaraSlim App',
        'env' => 'local',
        'debug' => true,
        'url' => 'http://localhost',
        'timezone' => 'UTC',
    ],

    'database' => [
        'default' => 'sqlite',
        'connections' => [
            'sqlite' => [
                'driver' => 'sqlite',
                'database' => __DIR__ . '/../storage/database.sqlite',
                'prefix' => '',
                'foreign_key_constraints' => true,
            ],
            'mysql' => [
                'driver' => 'mysql',
                'host' => 'localhost',
                'port' => '3306',
                'database' => 'laraslim',
                'username' => 'root',
                'password' => '',
                'charset' => 'utf8mb4',
                'collation' => 'utf8mb4_unicode_ci',
                'prefix' => '',
            ],
        ],
    ],

    'cache' => [
        'default' => 'file',
        'stores' => [
            'file' => [
                'driver' => 'file',
                'path' => __DIR__ . '/../storage/cache',
            ],
        ],
    ],

    'view' => [
        'paths' => [
            __DIR__ . '/../resources/views',
        ],
        'compiled' => __DIR__ . '/../storage/cache',
    ],

    'providers' => [
        // Lista de service providers
    ],
];