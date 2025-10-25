<?php
declare (strict_types = 1);

namespace app\middleware;

use xiaodi\JWTAuth\Facade\Jwt;
use app\model\Sessions;

class Auth
{
    /**
     * JWT authentication middleware
     *
     * @param \think\Request $request
     * @param \Closure       $next
     * @return Response
     */
    public function handle($request, \Closure $next)
    {
        try {
            // Retrieve token from header
            $token = $request->header('Authorization', '');
            
            if (empty($token)) {
                \Api::fail('Authentication token not provided', 401);
            }
            
            // Remove the "Bearer " prefix
            $token = str_replace('Bearer ', '', $token);
            
            // Validate token
            Jwt::verify($token);
            
            // Fetch token object
            $tokenObj = Jwt::getToken();
            
            // Verify that the session exists
            $session = Sessions::where('access_token_hash', md5($token))
                ->where('expires_at', '>', date('Y-m-d H:i:s'))
                ->find();
            
            if (!$session) {
                \Api::fail('Session expired or not found', 401);
            }
            
            // Inject user data into the request
            $request->userId = $tokenObj->getClaim('user_id');
            $request->userAddress = $tokenObj->getClaim('address');
            
            return $next($request);
            
        } catch (\Exception $e) {
            \Api::fail('Authentication failed', 401);
        }
    }
}

