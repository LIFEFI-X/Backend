<?php

namespace app;

use think\db\exception\DataNotFoundException;
use think\db\exception\ModelNotFoundException;
use think\exception\Handle;
use think\exception\HttpException;
use think\exception\HttpResponseException;
use think\exception\ValidateException;
use think\Response;
use Throwable;

/**
 * Application exception handler.
 */
class ExceptionHandle extends Handle
{
    /**
     * Exception classes that should not be logged.
     * @var array
     */
    protected $ignoreReport = [
        HttpException::class,
        HttpResponseException::class,
        ModelNotFoundException::class,
        DataNotFoundException::class,
        ValidateException::class,
    ];

    /**
     * Record exception information (logs or other channels).
     *
     * @access public
     * @param Throwable $exception
     * @return void
     */
    public function report(Throwable $exception): void
    {
        // Use the built-in handler to record exception logs.
        //parent::report($exception);
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @access public
     * @param \think\Request $request
     * @param Throwable       $e
     * @return Response
     */
    public function render($request, Throwable $e): Response
    {
        // Add custom exception handling logic.
        if ($e instanceof HttpResponseException) {
            return $e->getResponse();
        } else {
            $error = [
                'code' => $e->getCode(),
                'msg' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'request' => [  // Request information.
                    'method' => $request->method(),
                    'pathinfo' => $request->pathinfo(),
                    'controller' => $request->controller(),
                    'action' => $request->action(),
                    'header' => $request->header(config('trace.monitor_header', 'Authorization'), ''),
                    'param' => $request->all()
                ]
            ];
            $request_id = uniqid();
            trace('[' . $request_id . ']' . json_encode($error), 'api');  // Write to log.
            if (env('app_debug')) { // Debug mode.
                $error['request_id'] = $request_id;
                $error['trace'] = $e->getTrace();
                \Api::fail('Internal Server Error', 500, $error);
            } else {  // Non-debug mode.
                \Api::fail('Network error', 500, ['request_id' => $request_id]);
            }
        }
        // Defer other errors to the default handler.
        return parent::render($request, $e);
    }
}