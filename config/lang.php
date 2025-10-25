<?php
// +----------------------------------------------------------------------
// | Multi-language settings
// +----------------------------------------------------------------------

return [
    // Default language
    'default_lang'    => env('lang.default_lang', 'en-us'),
    // Allowed languages
    'allow_lang_list' => [],
    // Variable name for automatic language detection
    'detect_var'      => 'lang',
    // Whether to store selection in a cookie
    'use_cookie'      => true,
    // Cookie variable for language
    'cookie_var'      => 'think_lang',
    // Header variable for language
    'header_var'      => 'think-lang',
    // Extended language packs
    'extend_list'     => [],
    // Map Accept-Language values to language pack names
    'accept_language' => [
        'zh-hans-cn' => 'zh-cn',
    ],
    // Enable language grouping
    'allow_group'     => false,
];


