<?php

// +----------------------------------------------------------------------
// | Log settings
// +----------------------------------------------------------------------
return [
    // Default log channel
    'default'      => env('log.channel', 'file'),
    // Log levels to record
    'level'        => [],
    // Channels mapped for specific log types ['error'=>'email', ...]
    'type_channel' => [],
    // Disable global log writing
    'close'        => false,
    // Global log processor (supports closures)
    'processor'    => null,

    // Log channel list
    'channels'     => [
        'file' => [
            // Log driver type
            'type'           => 'File',
            // Log storage directory
            'path'           => '',
            // Write logs to a single file
            'single'         => false,
            // Independent log levels
            'apart_level'    => [],
            // Maximum number of log files
            'max_files'      => 0,
            // Record logs in JSON format
            'json'           => false,
            // Log processor
            'processor'      => null,
            // Disable log writing for this channel
            'close'          => false,
            // Log output format
            'format'         => '[%s][%s] %s',
            // Whether to write logs in real time
            'realtime_write' => false,
        ],
        // Other log channel configurations
    ],

];

