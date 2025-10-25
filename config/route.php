<?php
// +----------------------------------------------------------------------
// | Route settings
// +----------------------------------------------------------------------

return [
    // pathinfo delimiter
    'pathinfo_depr'         => '/',
    // URL pseudo-static suffix
    'url_html_suffix'       => 'html',
    // URL common parameter mode (for auto generation)
    'url_common_param'      => true,
    // Enable lazy route parsing
    'url_lazy_route'        => false,
    // Force the use of routes
    'url_route_must'        => true,
    // Merge route rules
    'route_rule_merge'      => false,
    // Require complete route match
    'route_complete_match'  => true,
    // Controller layer name
    'controller_layer'      => 'controller',
    // Name of the empty controller
    'empty_controller'      => 'Error',
    // Append controller suffix
    'controller_suffix'     => false,
    // Default route variable pattern
    'default_route_pattern' => '[\w\.]+',
    // Enable request caching; true caches automatically and supports custom rules
    'request_cache_key'     => false,
    // Request cache TTL
    'request_cache_expire'  => null,
    // Global request cache exclusion rules
    'request_cache_except'  => [],
    // Default controller name
    'default_controller'    => 'Index',
    // Default action name
    'default_action'        => 'index',
    // Action method suffix
    'action_suffix'         => '',
    // Default handler for JSONP responses
    'default_jsonp_handler' => 'jsonpReturn',
    // Default JSONP callback parameter
    'var_jsonp_handler'     => 'callback',
];

