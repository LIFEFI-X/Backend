<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2019 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------

// [ Application entry file ]
namespace think;

require __DIR__ . '/../vendor/autoload.php';

// ========== CORS handling START ==========
// Set CORS response headers (applies to all requests)
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, PATCH, OPTIONS');
header('Access-Control-Allow-Headers: DNT, User-Agent, X-Requested-With, If-Modified-Since, Cache-Control, Content-Type, Range, Authorization, X-CSRF-TOKEN, Content-Language, Accept-Language, If-Match, If-None-Match, If-Unmodified-Since, Origin, Accept');
header('Access-Control-Max-Age: 86400');
header('Access-Control-Allow-Credentials: false');

// Handle OPTIONS preflight request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}
// ========== CORS handling END ==========

// Run the HTTP application and respond
$http = (new App())->http;

$response = $http->run();

$response->send();

$http->end($response);


