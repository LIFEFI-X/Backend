<?php

namespace app\middleware;

use  \xy_jx\Utils\Sundry;

class Throttle
{
    /**
     * Rate limiting
     * @param $request
     * @param \Closure $next
     * @param int $limit Number of allowed requests within the window
     * @param string $time Time window unit s|m|h|d
     * @param string $key Key used for namespacing group/global throttles
     * @return mixed
     * @throws \Exception
     */
    public function handle($request, \Closure $next, int $limit = 3, string $time = 's', string $key = '')
    {
        $key = $key ?? md5($request->ip() . $request->baseUrl());
        // Apply rate limiting
        if (!Sundry::restrict([], $key, $limit, $time))
            \Api::fail('Too many requests', 429);

        // Business logic
        return $next($request);
    }

}
