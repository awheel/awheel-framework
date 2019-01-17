<?php

namespace awheel;

use awheel\Http\Request;
use awheel\Console\Input;
use awheel\Console\Output;
use awheel\Http\Kernel as HttpKernel;
use awheel\Console\Kernel as ConsoleKernel;

/**
 * Awheel App
 *
 * @package awheel
 */
class App extends Container
{
    const VERSION = '1.5.0';

    /**
     * 应用名称
     *
     * @var string
     */
    protected $name;

    /**
     * 根目录
     *
     * @var string
     */
    public $basePath;

    /**
     * 系统运行环境
     *
     * @var string
     */
    protected $environment;

    /**
     * 应用配置
     *
     * @var array
     */
    protected $config = [];

    /**
     * 系统级别组件
     *
     * @var array
     */
    protected $component = [
        'awheel\Support\LogComponent'
    ];

    /**
     * App constructor.
     *
     * @param $path
     * @param $environment
     */
    public function __construct($path, $environment)
    {
        // 记录程序运行环境
        $this->basePath = $path;
        $this->environment = $environment;
        $this->name = $this->configGet('app.name', 'awheel');

        mb_internal_encoding('UTF-8');
        date_default_timezone_set($this->configGet('app.timezone', 'Asia/Shanghai'));

        return $this;
    }

    /**
     * 注册 Kernel
     *
     * @return $this
     */
    public function registerKernel()
    {
        if ($this->runningInConsole()) {
            $this->register('ConsoleKernel', new ConsoleKernel($this));
        }
        else {
            $this->register('HttpKernel', new HttpKernel($this));
        }

        return $this;
    }

    /**
     * 启动应用
     *
     * @return $this
     */
    public function bootstrap()
    {
        // 单例
        static::setInstance($this);

        // 加载组件
        $component = array_merge($this->component, $this->configGet('app.component'));
        foreach ($component as $item) {
            $class = new $item;
            if (!$class instanceof Component) continue;

            $accessor = $class->getAccessor();
            $instances = $class->register();

            if (is_object($instances)) {
                static::register($accessor, $instances);
                continue;
            }

            if (!is_array($instances)) continue;
            foreach ($instances as $abstract => $instance) {
                static::register($accessor.'.'.$abstract, $instance);
            }
        }

        return $this;
    }

    /**
     * 运行应用
     *
     * @return bool
     * @throws \Exception
     */
    public function run()
    {
        if ($this->runningInConsole()) {
            $kernel = $kernel = static::make('ConsoleKernel');

            $input = new Input();
            $output = new Output();

            $status = $kernel->handle($input, $output);

            $kernel->terminate($input, $status);

            exit($status);
        }
        else {
            $kernel = static::make('HttpKernel');

            $request = Request::createFromGlobals();
            $request::enableHttpMethodParameterOverride();
            $request->enableHttpRequestParameterOverride();
            $response = $kernel->handle($request);

            $response->send();

            $kernel->terminate($request, $response);
        }

        return true;
    }

    /**
     * 是否允许在 控制台下面
     *
     * @return bool
     */
    public function runningInConsole()
    {
        return php_sapi_name() == 'cli';
    }

    /**
     * 获取当前运行环境
     *
     * @return string
     */
    public function environment()
    {
        return $this->environment;
    }

    /**
     * 获取应用名称
     *
     * @return string
     */
    public function name()
    {
        return empty($this->name) ? 'awheel' : $this->name;
    }

    /**
     * 获取配置, example: configGet("cache.config.redis.host", 'default_host');
     *
     * @param $key
     * @param $default
     *
     * @return array|mixed|string
     */
    public function configGet($key, $default = null)
    {
        $keys = explode('.', $key);
        $keysCount = count($keys);
        $config = $this->loadConfigure($keys[0]);

        for ($i = 1; $i < $keysCount; $i++) {
            $config = isset($config[$keys[$i]]) ? $config[$keys[$i]] : 'NOT_HAVE';
        }

        return $config === 'NOT_HAVE' ? $default : $config;
    }

    /**
     * 设置配置, 仅本次运行有效, example: configSet("cache.config.redis.host", 'new_host');
     *
     * @param $key
     * @param $value
     *
     * @return mixed
     */
    public function configSet($key, $value)
    {
        $keys = explode('.', $key);
        $this->loadConfigure($keys[0]);
        $config = &$this->config;

        while (count($keys) > 1) {
            $key = array_shift($keys);
            if (!isset($config[$key]) || ! is_array($config[$key])) {
                $config[$key] = [];
            }

            $config = &$config[$key];
        }

        $config[array_shift($keys)] = $value;

        return $value;
    }

    /**
     * 还原被 configSet 修改的设置
     *
     * @param $key
     *
     * @return mixed
     */
    public function configRevert($key)
    {
        if (!$key) return false;

        return $this->loadConfigure(current(explode('.', $key)), true);
    }

    /**
     * 加载配置项配置
     *
     * @param  string $item 配置项
     * @param  boolean $force 强制重新读取
     *
     * @return $this
     */
    protected function loadConfigure($item, $force = false)
    {
        if (array_key_exists($item, $this->config) && $force == false) {
            return $this->config[$item];
        }

        $config = [];
        $config_file = sprintf("%s/config/%s.php", $this->basePath, $item);
        if (is_file($config_file)) {
            $config = require $config_file;
        }

        $env_config = [];
        $env_config_file = sprintf("%s/config/%s/%s.php", $this->basePath, $this->environment, $item);
        if (is_file($env_config_file)) {
            $env_config = require $env_config_file;
        }

        $this->config[$item] = array_replace_recursive($config, $env_config);

        return $this->config[$item];
    }
}
