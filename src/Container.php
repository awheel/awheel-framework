<?php

namespace light;

use Redis;
use Monolog;
use Closure;
use light\Soa\Soa;
use Monolog\Logger;
use light\View\View;
use light\Cache\Cache;
use light\Http\Kernel;
use light\Http\Request;
use light\Http\Response;
use light\Routing\Router;
use light\Console\Kernel as ConsoleKernel;

/**
 * 容器
 *
 * @package light
 */
class Container
{
    /**
     * 应用实例
     *
     * @var
     */
    protected static $instance;

    /**
     * 其它组件实例
     *
     * @var array
     */
    protected static $instances = [];

    /**
     * 设置应用实例
     *
     * @param Container $container
     */
    public static function setInstance(Container $container)
    {
        static::$instance = $container;
    }

    /**
     * 获取应用实例
     *
     * @return \light\App
     */
    public static function getInstance()
    {
        return static::$instance;
    }

    /**
     * 注册其它组件实例
     *
     * @param $abstract
     * @param $instance
     */
    public function register($abstract, $instance)
    {
        static::$instances[$abstract] = $instance;
    }

    /**
     * 获取其它组件实例, Ioc
     * example: app()->make('cache')->get('key'), $this->make('cache')->get('key');
     *
     * @param $abstract
     *
     * @return null|App|Logger|Cache|Soa|Router|View|Request|Response|Redis|Kernel|ConsoleKernel
     */
    public function make($abstract)
    {
        $instance = isset(static::$instances[$abstract]) ? static::$instances[$abstract] : null;
        if ($instance instanceof Closure) {
            $instance = $instance();
            self::register($abstract, $instance);
        }

        return $instance;
    }
}
