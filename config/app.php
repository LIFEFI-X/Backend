<?php
// +----------------------------------------------------------------------
// | Application settings
// +----------------------------------------------------------------------

return [
    // Application host
    'app_host'         => env('app.host', ''),
    // Application namespace
    'app_namespace'    => '',
    // Whether to enable routing
    'with_route'       => true,
    // Default application
    'default_app'      => 'index',
    // Default timezone
    'default_timezone' => 'Asia/Shanghai',

    // Application mapping (effective in multi-app mode)
    'app_map'          => [],
    // Domain binding (effective in multi-app mode)
    'domain_bind'      => [],
    // Applications denied from URL access (multi-app mode)
    'deny_app_list'    => [],

    // Template file for exception pages
    'exception_tmpl'   => app()->getThinkPath() . 'tpl/think_exception.tpl',

    // Error message displayed when not in debug mode
    'error_message'    => 'Page error! Please try again later~',

    // Display detailed error messages
    'show_error_msg'   => false,

    // Whether data should be encrypted
    'is_it_encrypted'  => false,

    // Frontend domain (used for knowledge-transfer redirect URLs)
    'frontend_url'     => env('app.frontend_url', 'https://www.lifefi.io'),
];



