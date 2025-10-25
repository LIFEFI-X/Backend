<?php

return [
    // Default database connection configuration
    'default'         => env('database.driver', 'mysql'),

    // Custom time query rules
    'time_query_rule' => [],

    // Auto-write timestamp fields
    // true enables auto type detection, false disables it
    // String value explicitly sets the time field type: int/timestamp/datetime/date
    'auto_timestamp'  => true,

    // Default date-time format when reading time fields
    'datetime_format' => 'Y-m-d H:i:s',

    // Database connection configuration
    'connections'     => [
        'mysql' => [
            // Database type
            'type'            => env('database.type', 'mysql'),
            // Server address
            'hostname'        => env('database.hostname', '43.134.71.205'),
            // Database name
            'database'        => env('database.database', 'nft'),
            // Username
            'username'        => env('database.username', 'nft'),
            // Password
            'password'        => env('database.password', 'sXpQEbriGd2ANW7A'),
            // Port
            'hostport'        => env('database.hostport', '3306'),
            // Connection parameters
            'params'          => [
                // Resolve MySQL 8.0 authentication issues
                \PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT => false,
                \PDO::MYSQL_ATTR_SSL_CA => null,
            ],
            // Database charset (defaults to UTF-8)
            'charset'         => env('database.charset', 'utf8'),
            // Database table prefix
            'prefix'          => env('database.prefix', ''),

            // Deployment mode: 0 centralized (single server), 1 distributed (master/slave)
            'deploy'          => 0,
            // Enable read/write separation (master/slave only)
            'rw_separate'     => false,
            // Number of master servers when separated
            'master_num'      => 1,
            // Specify slave server index
            'slave_no'        => '',
            // Strictly check whether fields exist
            'fields_strict'   => true,
            // Reconnect automatically on disconnect
            'break_reconnect' => false,
            // Log SQL statements
            'trigger_sql'     => env('app_debug', false),
            // Enable field cache
            'fields_cache'    => false,
        ],

        // Additional database configuration
    ],
];

