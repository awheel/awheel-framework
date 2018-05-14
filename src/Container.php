<?php

namespace awheel;

use Closure;
use Monolog\Logger;
use awheel\Http\Request;
use awheel\Http\Response;
use awheel\Routing\Router;
use ReflectionClass;
use awheel\Http\Kernel as HttpKernel;
use awheel\Console\Kernel as ConsoleKernel;
use ReflectionParameter;
use Exception;

/**
 * 容器
 *
 * @package awheel
 */
class Container
{
    /**
     * 应用实例
     *
     * @var App
     */
    protected static $instance;

    /**
     * 内置类别名
     *
     * @var array
     */
    protected $alias = [
        'router' => Routing\Router::class,
        'request' => Http\Request::class,
        'response' => Http\Response::class,
    ];

    /**
     * 其它组件实例
     *
     * @var array
     */
    protected static $instances = [];

    /**
     * 设置应用实例
     *
     * @param App $container
     *
     * @return boolean
     */
    static public function setInstance(App $container)
    {
        static::$instance = $container;

        return true;
    }

    /**
     * 获取应用实例
     *
     * @return App
     */
    static public function getInstance()
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
    static public function register($abstract, $instance)
    {
        static::$instances[$abstract] = $instance;

        return true;
    }

    /**
     * 获取其它组件实例
     *
     * @param $abstract
     *
     * @return App|Router|Request|Response|HttpKernel|ConsoleKernel|Logger
     */
    static public function make($abstract)
    {
        $instance = isset(static::$instances[$abstract]) ? static::$instances[$abstract] : null;
        if ($instance instanceof Closure) {
            $instance = $instance();
            self::register($abstract, $instance);
        }

        return $instance;
    }

    public function build($concrete)
    {
        // 闭包
        if ($concrete instanceof Closure) {
            return $concrete($this);
        }

        $reflector = new ReflectionClass($concrete);

        // 无法实例化
        if (!$reflector->isInstantiable()) {
            // todo throw Exception
            throw new Exception();
        }

        $constructor = $reflector->getConstructor();

        // 没有参数
        if (is_null($constructor)) {
            return new $concrete;
        }

        $dependencies = $constructor->getParameters();
        $instances = $this->resolveDependencies($dependencies);

        return $reflector->newInstanceArgs($instances);
    }

    /**
     * 依赖解析
     *
     * @param array $dependencies
     *
     * @return array
     */
    public function resolveDependencies(array $dependencies)
    {
        $result = [];
        foreach ($dependencies as $dependency) {
            $result[] = is_null($class = $dependency->getClass())
                ? $this->resolvePrimitive($dependency)
                : $this->resolveClass($dependency);
        }

        return $result;
    }

    /**
     * 依赖其他参数或其他未知无法解析的东西
     *
     * @param ReflectionParameter $parameter
     *
     * @return mixed
     * @throws Exception
     */
    public function resolvePrimitive(ReflectionParameter $parameter)
    {
        if ($parameter->isDefaultValueAvailable()) {
            return $parameter->getDefaultValue();
        }

        throw new Exception($parameter);
    }

    /**
     * 反射类名解析
     *
     * @param ReflectionParameter $parameter
     *
     * @return App|ConsoleKernel|HttpKernel|Request|Response|Router|mixed|Logger
     * @throws Exception
     */
    public function resolveClass(ReflectionParameter $parameter)
    {
        try {
            return $this->make($parameter->getClass()->name);
        }

        catch (Exception $e) {
            if ($parameter->isOptional()) {
                return $parameter->getDefaultValue();
            }

            throw $e;
        }
    }

}
