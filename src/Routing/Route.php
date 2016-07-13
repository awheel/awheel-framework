<?php

namespace light\Routing;

class Route
{
    public $uri;

    public $method = [];

    public $action;

    public $name;

    public $prefix;

    protected $middleware;

    public $default = [];

    public $where = [];

    protected $index;

    public function __construct($method, $uri, $action, $index)
    {
        $this->methods  = $method;
        $this->uri  = $uri;
        $this->action = $action;
        $this->index = $index;
    }

    /**
     * 设置路由中间件
     *
     * @param string|array $middleware
     *
     * @return $this
     */
    public function middleware($middleware)
    {
        $this->middleware = $middleware;

        return $this;
    }

    /**
     * 设置路由名称
     *
     * @param $name
     *
     * @return $this
     */
    public function name($name)
    {
        $this->name = $name;

        return $this;
    }

    public function secure()
    {
        return $this;
    }

    /**
     * 设置路由前缀
     *
     * @param $prefix
     *
     * @return $this
     */
    public function prefix($prefix)
    {
        $this->prefix = $prefix;

        return $this;
    }

    public function getName()
    {
        return $this->name;
    }

    public function getUri()
    {
        return $this->uri;
    }
}
