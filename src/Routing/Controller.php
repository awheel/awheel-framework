<?php

namespace light\Routing;

use BadMethodCallException;
use light\Exceptions\NotFoundHttpException;

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
     *
     * @throws NotFoundHttpException
     */
    public function missingMethod($method, $params)
    {
        throw new NotFoundHttpException("Method [$method] does not exist.");
    }

    /**
     * 魔调
     *
     * @param $name
     * @param $arguments
     *
     * @throws BadMethodCallException
     */
    public function __call($name, $arguments)
    {
        throw new BadMethodCallException("Method [$name}] does not exist.");
    }
}
