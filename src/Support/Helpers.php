<?php

use light\App;
use Monolog\Logger;
use light\Http\Request;
use light\Http\Response;
use light\Routing\Router;
use light\Http\Kernel as HttpKernel;
use light\Console\Kernel as ConsoleKernel;

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

    return App::getInstance()->make($make);
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
        return isset($params[$m[1]]) ? ArrayFactory::pull($params, $m[1]) : $m[0];
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

if (! function_exists('dd')) {
    /**
     * 打印数据, 并停止执行.
     */
    function dd()
    {
        echo '<pre>';
        array_map('var_dump', func_get_args());
        echo '</pre>';
        exit;
    }
}
