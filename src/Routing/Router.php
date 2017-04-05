<?php

namespace awheel\Routing;

use Closure;
use FastRoute;
use awheel\Pipeline;
use awheel\Http\Request;
use awheel\Http\Response;
use awheel\Exceptions\NotFoundHttpException;
use awheel\Exceptions\NotAllowCallException;

/**
 * 路由
 *
 * @package awheel\Routing
 */
class Router
{
    /**
     * 路由标识符
     *
     * @var int
     */
    protected $identifier = 1;

    /**
     * 设定的路由
     *
     * @var array
     */
    public $routes = [];

    /**
     * 命名的路由
     *
     * @var array
     */
    public $namedRoutes = [];

    /**
     * 路由组属性
     *
     * @var array
     */
    protected $groupAttributes = [];

    /**
     * 参数匹配魔术
     *
     * @var array
     */
    public $patterns = [];

    /**
     * 当前路由
     *
     * @var array
     */
    protected $currentRoute = [];

    /**
     * 控制器的命名空间
     *
     * @var string
     */
    protected $namespace = 'app\\Controller';

    /**
     * 路由中间件
     *
     * @var array
     */
    protected $middlewareNamespace = 'app\\Middleware';

    /**
     * 设置 GET 请求路由
     *
     * @param $uri
     * @param $action
     *
     * @return $this
     */
    public function get($uri, $action)
    {
        $this->addRoute(['GET', 'HEAD'], $uri, $action);

        return $this;
    }

    /**
     * 设置 POST 请求路由
     *
     * @param $uri
     * @param $action
     *
     * @return $this
     */
    public function post($uri, $action)
    {
        $this->addRoute(['POST'], $uri, $action);

        return $this;
    }

    /**
     * 设置 PUT 请求路由
     *
     * @param $uri
     * @param $action
     *
     * @return $this
     */
    public function put($uri, $action)
    {
        $this->addRoute(['PUT'], $uri, $action);

        return $this;
    }

    /**
     * 设置 PATCH 请求路由
     *
     * @param $uri
     * @param $action
     *
     * @return $this
     */
    public function patch($uri, $action)
    {
        $this->addRoute(['PATCH'], $uri, $action);

        return $this;
    }

    /**
     * 设置 DELETE 请求路由
     *
     * @param $uri
     * @param $action
     *
     * @return $this
     */
    public function delete($uri, $action)
    {
        $this->addRoute(['DELETE'], $uri, $action);

        return $this;
    }

    /**
     * 设置 OPTIONS 请求路由
     *
     * @param $uri
     * @param $action
     *
     * @return $this
     */
    public function options($uri, $action)
    {
        $this->addRoute(['OPTIONS'], $uri, $action);

        return $this;
    }

    /**
     * 设置 全模式 路由
     *
     * @param $uri
     * @param $action
     *
     * @return $this
     */
    public function any($uri, $action)
    {
        $this->addRoute(['GET', 'HEAD', 'POST', 'PUT', 'PATCH', 'DELETE'], $uri, $action);

        return $this;
    }

    /**
     * 设置路由参数匹配规则
     *
     * @param $param
     * @param $pattern
     */
    public function pattern($param, $pattern)
    {
        $this->patterns[$param] = $pattern;
    }

    /**
     * 设置 restful 路由, 自带 GET/POST/PUT/DELETE 四个方法
     *
     * @param $uri
     * @param $action
     *
     * @return $this
     */
    public function restful($uri, $action)
    {
        $uri = rtrim($uri, '/');
        $control = strtolower(strtr($action, ['Controller' => '']));

        $this->addRoute('GET', $uri, ['uses' => $action.'@index', 'as' => $control.'.index']);
        $this->addRoute('GET', $uri.'/{id}', ['uses' => $action.'@show', 'as' => $control.'.show']);

        $this->addRoute('GET', $uri.'/create', ['uses' => $action.'@create', 'as' => $control.'.create']);
        $this->addRoute('POST', $uri, $action.'@store');

        $this->addRoute('GET', $uri.'/{id}/edit', ['uses' => $action.'@edit', 'as' => $control.'.edit']);
        $this->addRoute('PUT', $uri.'/{id}', $action.'@update');

        $this->addRoute('DELETE', $uri.'/{id}', $action.'@destroy');

        return $this;
    }

    /**
     * 设置路由组
     *
     * @param array $attributes
     * @param Closure $callback
     */
    public function group(array $attributes, Closure $callback)
    {
        $parentGroupAttributes = $this->groupAttributes;

        $prefix = (isset($this->groupAttributes['prefix']) ? $this->groupAttributes['prefix'] : '').'/'.
            (isset($attributes['prefix']) ? $attributes['prefix'] : '');

        $middleware = (isset($this->groupAttributes['middleware']) ? $this->groupAttributes['middleware'] : '').'|'.
            (isset($attributes['middleware']) ? $attributes['middleware'] : '');

        $namespace = (isset($this->groupAttributes['namespace']) ? $this->groupAttributes['namespace'] : '').'\\'.
            (isset($attributes['namespace']) ? $attributes['namespace'] : '');

        $this->groupAttributes = [
            'prefix' => $prefix,
            'middleware' => $middleware,
            'namespace' => $namespace
        ];

        call_user_func($callback, $this);

        $this->groupAttributes = $parentGroupAttributes;
    }

    /**
     * 添加路由
     *
     * @param $method
     * @param $uri
     * @param $action
     *
     * @return $this
     */
    public function addRoute($method, $uri, $action)
    {
        $action = $this->parseAction($action);

        if (isset($this->groupAttributes)) {
            if (isset($this->groupAttributes['prefix'])) {
                $uri = trim($this->groupAttributes['prefix'], '/').'/'.trim($uri, '/');
            }

            $action = $this->mergeGroupAttributes($action);
        }

        $uri = '/'.trim($uri, '/');

        if (isset($action['as'])) {
            $this->namedRoutes[$action['as']] = $uri;
        }

        $this->routes[$this->identifier] = ['method' => $method, 'uri' => $uri, 'action' => $action];
        $this->identifier++;

        return $this;
    }

    /**
     * 解析请求
     *
     * @param $action
     *
     * @return array
     */
    protected function parseAction($action)
    {
        if (is_string($action)) {
            return ['uses' => $action];
        }
        elseif (!is_array($action)) {
            return [$action];
        }

        return $action;
    }

    /**
     * 合并新设置的路由组
     *
     * @param array $action
     *
     * @return array
     */
    protected function mergeGroupAttributes(array $action)
    {
        return $this->mergeNamespaceGroup($this->mergeMiddlewareGroup($action));
    }

    /**
     * 合并新设置的路由组的命名空间
     *
     * @param array $action
     *
     * @return array
     */
    protected function mergeNamespaceGroup(array $action)
    {
        if (isset($this->groupAttributes['namespace']) && isset($action['uses'])) {
            $namespace = trim($this->groupAttributes['namespace'], '\\');

            $action['uses'] = $namespace ? $namespace.'\\'.$action['uses'] : $action['uses'];
        }

        return $action;
    }

    /**
     * 合并路由组的中间件
     *
     * @param $action
     *
     * @return mixed
     */
    protected function mergeMiddlewareGroup(array $action)
    {
        if (isset($this->groupAttributes['middleware'])) {
            $groupMiddleware = trim($this->groupAttributes['middleware'], '|');

            if (isset($action['middleware'])) {
                $action['middleware'] = $groupMiddleware.'|'.$action['middleware'];
            }
            else {
                $action['middleware'] = $groupMiddleware;
            }
        }

        return $action;
    }

    /**
     * 任务分发, 路由系统基于 fast-route
     *
     * @param $request
     *
     * @return bool|Response|
     *
     * @throws \Exception
     */
    public function dispatch(Request $request)
    {
        $dispatcher = FastRoute\simpleDispatcher(function(FastRoute\RouteCollector $r) {
            foreach ($this->routes as $route) {
                $route['uri'] = preg_replace_callback('/\{((?!.*:).*)\}/isU', function ($matches) {
                    $param = $matches[1];
                    $pattern = isset($this->patterns[$param]) ? ':'.$this->patterns[$param] : '';
                    return sprintf("{%s%s}", $param, $pattern);
                }, $route['uri']);

                $r->addRoute($route['method'], $route['uri'], $route['action']);
            }
        });

        $method = $request->getMethod();
        $pathInfo = $request->getPathInfo();
        $routeInfo = $dispatcher->dispatch($method, $pathInfo);

        switch ($routeInfo[0]) {
            case FastRoute\Dispatcher::NOT_FOUND:
                throw new NotFoundHttpException('404 Not Found: '.$pathInfo, 404);
                break;

            case FastRoute\Dispatcher::METHOD_NOT_ALLOWED:
                throw new NotAllowCallException('Method not Allowed: '.$method, 405);
                break;

            case FastRoute\Dispatcher::FOUND:
                return $this->handleFoundRoute($routeInfo, $request);
                break;
        }

        return false;
    }

    /**
     * 处理匹配到的路由
     *
     * @param $route
     * @param $request
     *
     * @return Response
     */
    protected function  handleFoundRoute($route, Request $request)
    {
        $this->currentRoute = $route;

        if (isset($route[1]['middleware']) && !empty($route[1]['middleware'])) {
            $middleware = $route[1]['middleware'];
            $middleware = is_string($middleware) ? explode('|', $middleware) : (array) $middleware;
            $middleware = array_map(function ($m) { return $this->middlewareNamespace .'\\'. $m; }, $middleware);

            $response = (new Pipeline())->send($request)->through($middleware)
                ->then(function () use ($route, $request) {
                    return $this->callAction($route, $request);
                });
        }
        else {
            $response = $this->callAction($route, $request);
        }

        if (!$response instanceof Response) {
            $response = new Response($response);
        }

        return $response;
    }

    /**
     * 调用路由指定的方法或闭包
     *
     * @param $route
     * @param Request $request
     *
     * @return mixed
     */
    public function callAction($route, Request $request)
    {
        if ($request->isMethod('OPTIONS')) {
            return new Response(null, 200);
        }

        // 闭包
        foreach ($route[1] as $value) {
            if ($value instanceof Closure) {
                return call_user_func_array($value, $route[2]);
                break;
            }
        }

        list($class, $method) = explode('@', $route[1]['uses']);
        $controller = $this->namespace.'\\'.$class;
        $instance =  new $controller();

        if (!method_exists($instance, $method)) {
            return call_user_func_array([$instance, "missingMethod"], [$method, $route[2]]);
        }

        return call_user_func_array([$instance, $method], $route[2]);
    }
}
