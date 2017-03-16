<?php

namespace light;

use Closure;
use Monolog\Logger;
use light\Http\Request;
use light\Http\Response;
use light\Routing\Router;
use light\Http\Kernel as HttpKernel;
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
     *
     * @return boolean
     */
    public static function setInstance(Container $container)
    {
        static::$instance = $container;

        return true;
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
     *
     * @return boolean
     */
    public function register($abstract, $instance)
    {
        static::$instances[$abstract] = $instance;

        return true;
    }

    /**
     * 获取其它组件实例, Ioc
     * example: app('cache')->get('key'), app()->make('cache')->get('key'), $this->make('cache')->get('key');
     *
     * @param $abstract
     *
     * @return App|Router|Request|Response|HttpKernel|ConsoleKernel|Logger
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
