<?php

// +----------------------------------------------------------------------
// | Cache settings
// +----------------------------------------------------------------------

return [
    // Default cache driver
    'default' => env('cache.driver', 'file'),

    // Cache connection configuration
    'stores'  => [
        'file' => [
            // Driver type
            'type'       => 'File',
            // Cache storage directory
            'path'       => '',
            // Cache prefix
            'prefix'     => '',
            // Cache TTL (0 means persistent)
            'expire'     => 0,
            // Cache tag prefix
            'tag_prefix' => 'tag:',
            // Serialization handlers e.g. ['serialize', 'unserialize']
            'serialize'  => [],
        ],
        // Additional cache connections
        'redis'    =>    [
            'type'     => 'redis',
            'host'     => '127.0.0.1',
            'port'     => 6379,
            'password' => '',
            'select'   => 1,
            // Global cache TTL (0 indicates permanent)
            'expire'   => 0,
            // Cache tag prefix
            'tag_prefix' => 'tag:',
            // Cache prefix
            'prefix'   => '',
            // Serialization handlers e.g. ['serialize', 'unserialize']
            'serialize'  => [],
        ],
    ],
];

