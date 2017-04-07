<?php

use awheel\App;
use Monolog\Logger;
use awheel\Support\Arr;
use awheel\Http\Request;
use awheel\Http\Response;
use awheel\Routing\Router;
use awheel\Http\Kernel as HttpKernel;
use awheel\Console\Kernel as ConsoleKernel;

/**
 * 返回应用实例, 或应用组件
 *
 * @param $make
 *
 * @return App|Router|Request|Response|HttpKernel|ConsoleKernel|Logger
 */
function app($make = null)
{
    if (is_null($make)) {
        return App::getInstance();
    }

    return App::make($make);
}

/**
 * 路由名称转为 url, 参数为路由定义时的参数, 多余的使用 http query 链接
 *
 * @param $name
 * @param array $params
 *
 * @return mixed|string
 */
function route($name, $params = [])
{
    $namedRoutes = app('router')->namedRoutes;
    if (!isset($namedRoutes[$name])) return $name;

    $uri = $namedRoutes[$name];
    $uri = preg_replace_callback('/\{(.*?)(:.*?)?(\{[0-9,]+\})?\}/', function ($m) use (&$params) {
        return isset($params[$m[1]]) ? Arr::pull($params, $m[1]) : $m[0];
    }, $uri);

    if (! empty($params)) {
        $uri .= '?'.http_build_query($params);
    }

    return rtrim(app()->configGet('app.base_url'), '/').$uri;
}

/**
 * 获取 basePath 或基于 basePath 的目录
 *
 * @param string $path
 *
 * @return string
 */
function base_path($path = '')
{
    return app()->basePath.($path ? '/'.ltrim($path, '/') : '');
}
