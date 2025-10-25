<?php


namespace app\controller;


class Error
{
    /**
     * Controller does not exist.
     * @param $method
     * @param $args
     */
    public function __call($method, $args)
    {
        \Api::fail('Resource not found', 410);
    }
}
