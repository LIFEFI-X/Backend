<?php

/**
 * Code conventions (customize as needed):
 * 200  Operation succeeded
 * 204  No data
 * 400  Operation failed / generic error
 * 401  User not logged in or token invalid (header)
 * 402  Data validation error
 * 403  Permission check failed
 * 404  Parameter error or resource not found (possibly permission related)
 * 405  API path invalid or missing
 * 407  Verify request validation failed (header)
 * 410  Resource not found (controller or method missing)
 * 429  Too many requests issued within the time window (rate limit)
 * 500  Internal server error (check returned request_id for troubleshooting)
 * Class Api
 */
class Api
{
    /**
     * @param int $code Business status code.
     * @param mixed $data Response payload {}/[].
     * @param string $msg Response message.
     */
    private static function return(int $code = 200, $data = [], string $msg = 'success')
    {
        $json = [
            'code' => $code,
            'message' => $msg,
            'data' => $data,
            'timestamp' => intval(microtime(true) * 1000), // Millisecond timestamp.
        ];
        if (config('app.is_it_encrypted')) {
            $Openssl = new \xy_jx\Utils\Openssl();
            $json = $Openssl::encrypt($json); // Encrypt with private key.
        }
        throw new \think\exception\HttpResponseException(json($json));
    }

    /**
     * Success helper.
     * @param mixed $data
     * @param int $code
     * @param string $msg
     */
    public static function success($data = [], int $code = 200, string $msg = 'success')
    {
        return self::return($code, $data, $msg);
    }

    /**
     * Failure helper.
     * @param string $msg
     * @param int $code
     * @param mixed $data
     */
    public static function fail(string $msg = 'fail', int $code = 400, $data = [])
    {
        return self::return($code, $data, $msg);
    }
}
