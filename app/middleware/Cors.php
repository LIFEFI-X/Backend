<?php

namespace app\middleware;

use Closure;
use think\Response;

/**
 * Cross-origin middleware
 */
class Cors
{
    /**
     * CORS response header configuration
     * @var array
     */
    private $headers = [
        'Access-Control-Allow-Origin' => '*',
        'Access-Control-Allow-Methods' => 'GET, POST, PUT, DELETE, PATCH, OPTIONS',
        'Access-Control-Allow-Headers' => 'DNT, User-Agent, X-Requested-With, If-Modified-Since, Cache-Control, Content-Type, Range, Authorization, X-CSRF-TOKEN, Content-Language, Accept-Language, If-Match, If-None-Match, If-Unmodified-Since, Origin, Accept',
        'Access-Control-Max-Age' => '86400',
        'Access-Control-Allow-Credentials' => 'false',
    ];

    /**
     * Handle request
     *
     * @param \think\Request $request
     * @param Closure $next
     * @return Response
     */
    public function handle($request, Closure $next)
    {
        // Handle OPTIONS preflight request
        if ($request->method(true) === 'OPTIONS') {
            return response('', 204)->header($this->headers);
        }

        // Continue processing the request and capture the response
        try {
            $response = $next($request);
        } catch (\Exception $e) {
            // Ensure response still contains CORS headers even when exceptions occur
            $response = json([
                'code' => 500,
                'message' => $e->getMessage(),
                'data' => [],
                'timestamp' => intval(microtime(true) * 1000)
            ], 500);
        }

        // Ensure the response is a Response object
        if (!$response instanceof Response) {
            $response = response($response);
        }

        // Append CORS headers to all responses (chain call to prevent overrides)
        foreach ($this->headers as $key => $value) {
            $response->header([$key => $value]);
        }

        return $response;
    }
}


