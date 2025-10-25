<?php
declare (strict_types=1);

namespace app;

use think\App;
use think\exception\ValidateException;
use think\Validate;

/**
 * Base controller.
 */
abstract class BaseController
{
    /**
     * Request instance.
     * @var \think\Request
     */
    protected $request;

    /**
     * Application instance.
     * @var \think\App
     */
    protected $app;

    /**
     * Whether to validate in batch.
     * @var bool
     */
    protected $batchValidate = false;

    /**
     * Controller middleware list.
     * @var array
     */
    protected $middleware = [];

    /**
     * Constructor.
     * @access public
     * @param App $app Application container.
     */
    public function __construct(App $app)
    {
        $this->app = $app;
        $this->request = $this->app->request;

        // Controller initialization.
        $this->initialize();
    }

    // Initialization hook.
    protected function initialize()
    {
    }

    /**
     * Validate data against rules.
     * @access protected
     * @param array        $data     Payload data.
     * @param string|array $validate Validator name or rules array.
     * @param array        $message  Custom messages.
     * @param bool         $batch    Whether to validate in batch.
     * @return void
     * @throws ValidateException
     */
    protected function validate(array $data, $validate, array $message = [], bool $batch = false)
    {
        if (is_array($validate)) {
            $v = new Validate();
            $v->rule($validate);
        } else {
            if (strpos($validate, '.')) {
                // Support validation scenes.
                [$validate, $scene] = explode('.', $validate);
            }
            $class = false !== strpos($validate, '\\') ? $validate : $this->app->parseClass('validate', $validate);
            $v = new $class();
            if (!empty($scene)) {
                $v->scene($scene);
            }
        }

        $v->message($message);

        // Enable batch validation if needed.
        if ($batch || $this->batchValidate) {
            $v->batch(true);
        }

        //  return $v->failException(true)->check($data);

        $v->failException(false)->check($data);

        if ($v->getError()) {
            \Api::fail($v->getError(), 402);
        }
    }

    /**
     * Retrieve request data with filtering, defaults, and validation.
     * @param array $params   [[parameter, default value]]
     * @param array $validate ['api|API' => 'require|email'] see TP6 validator syntax.
     * @param array $message  Custom messages such as ['name.require' => 'Name is required'].
     * @return array
     */
    protected function getParam(array $params, array $validate = [], array $message = []): array
    {
        if (config('app.is_it_encrypted') && $this->request->param('data')) {
            $Openssl = new \xy_jx\Utils\Openssl();
            $this->request->setRoute($Openssl::decrypt($this->request->param('data'))); // Decrypt with public key and assign to the request.
        }
        $p = [];
        foreach ($params as $key => $param) {
            if (is_array($param)) {
                if (strpos($param[0], '/')) {
                    [$name, $type] = explode('/', $param[0]);
                } else {
                    $name = $param[0];
                }
                $p[$name] = $this->request->param($param[0], $param[1] ?? null, $param[2] ?? '');
            } elseif (is_int($key)) {
                $p[$param] = $this->request->param($param);
            } else {
                if (strpos($key, '/')) {
                    [$name, $type] = explode('/', $key);
                } else {
                    $name = $key;
                }
                $p[$name] = $this->request->param($key, $param);
            }
        }
        if ($validate) {
            $this->validate($p, $validate, $message);
        }
        return $p;
    }

    /**
     * Handle calls to undefined methods.
     * @param $method
     * @param $args
     */
    public function __call($method, $args)
    {
        \Api::fail('Resource not found', 410);
    }
}