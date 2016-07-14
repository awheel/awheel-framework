<?php

use light\App;
use light\Soa\Soa;
use Monolog\Logger;
use light\View\View;
use light\Container;
use light\Http\Kernel;
use light\Cache\Cache;
use light\Http\Request;
use light\Http\Response;
use light\Routing\Router;
use light\Console\Kernel as ConsoleKernel;

/**
 * 返回应用实例, 或应用组件
 *
 * @param $make
 *
 * @return null|App|Logger|Cache|Soa|Router|View|Request|Response|Redis|Kernel|ConsoleKernel;
 */
function app($make = null)
{
    if (is_null($make)) {
        return Container::getInstance();
    }

    return Container::getInstance()->make($make);
}

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

/**
 * 重定向
 * todo 使用 Response
 *
 * @param $uri
 * @param int $status
 */
function redirect($uri, $status = 302)
{
    $uri = route($uri);
    header("Location: ".$uri, true, $status);
    exit();
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
    $namedRoutes = app()->make('router')->namedRoutes;
    if (!isset($namedRoutes[$name])) return $name;

    $uri = $namedRoutes[$name];

    $uri = preg_replace_callback('/\{(.*?)(:.*?)?(\{[0-9,]+\})?\}/', function ($m) use (&$params) {
        return isset($params[$m[1]]) ? array_pull($params, $m[1]) : $m[0];
    }, $uri);

    if (! empty($params)) {
        $uri .= '?'.http_build_query($params);
    }

    return rtrim(app()->configGet('app.base_url'), '/').$uri;
}

/**
 * 检查是否是移动设备
 *
 * @return null
 */
function detectMobile()
{
    $pattern = '/(iPad|iPhone|Android|Mobile|Meego|Nokia|Windows Phone|Silk|KFAPWI|RIM Tablet)/isU';
    $userAgent = app('request')->server('HTTP_USER_AGENT');

    if ($userAgent && preg_match($pattern, $userAgent)) {
        return true;
    }

    return false;
}

/**
 * 获取页面来源
 *
 * @param null $default
 *
 * @return null
 */
function referer($default = null)
{
    return app('request')->server('HTTP_REFERER', $default);
}

if (!function_exists('array_column')) {
    /**
     * 返回数组中指定的一列
     * xxx PHP 5 >= 5.5.0, PHP 7 自带
     *
     * @param $array
     * @param $column_name
     *
     * @return array
     */
    function array_column($array, $column_name)
    {
        return array_map(function($element) use($column_name) {return $element[$column_name];}, $array);
    }
}

/**
 * 使用 key 从一个数组获取一条数据, 并删除这条数据
 *
 * @param $array
 * @param $key
 * @param null $default
 *
 * @return null
 */
function array_pull(&$array, $key, $default = null)
{
    $value = isset($array[$key]) ? $array[$key] : $default;

    unset($array[$key]);

    return $value;
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
    return app()->basePath.($path ? '/'.ltrim('/', $path) : '');
}
