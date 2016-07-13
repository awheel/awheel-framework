<?php

namespace light\Routing;

use BadMethodCallException;

/**
 * 控制器基类
 *
 * @package light
 */
abstract class Controller
{
    /**
     * 当前请求的方法不存在时调用, 可自定义
     *
     * @param $method
     * @param $params
     */
    public function missingMethod($method, $params)
    {
        throw new BadMethodCallException("Method [$method] does not exist.");
    }

    /**
     * 魔调
     *
     * @param $name
     * @param $arguments
     */
    public function __call($name, $arguments)
    {
        throw new BadMethodCallException("Method [$name] does not exist.");
    }
}
