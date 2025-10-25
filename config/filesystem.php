<?php

return [
    // Default disk
    'default' => env('filesystem.driver', 'local'),
    // Disk list
    'disks'   => [
        'local'  => [
            'type' => 'local',
            'root' => app()->getRuntimePath() . 'storage',
        ],
        'public' => [
            // Disk type
            'type'       => 'local',
            // Disk path
            'root'       => app()->getRootPath() . 'public/storage',
            // Public URL corresponding to the disk path
            'url'        => '/storage',
            // Visibility
            'visibility' => 'public',
        ],
        // Additional disk configuration
    ],
];


