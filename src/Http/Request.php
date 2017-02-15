<?php

namespace light\Http;

/**
 * http 请求
 *
 * @package light
 */
class Request
{
    /**
     * HEAD 请求
     */
    const METHOD_HEAD = 'HEAD';

    /**
     * GET 请求
     */
    const METHOD_GET = 'GET';

    /**
     * POST 请求
     */
    const METHOD_POST = 'POST';

    /**
     * PUT 请求
     */
    const METHOD_PUT = 'PUT';

    /**
     * PATCH 请求
     */
    const METHOD_PATCH = 'PATCH';

    /**
     * DELETE 请求
     */
    const METHOD_DELETE = 'DELETE';

    /**
     * OPTIONS 请求
     */
    const METHOD_OPTIONS = 'OPTIONS';

    /**
     * 全部用户如入
     *
     * @var array
     */
    protected $input;

    /**
     * 用户请求
     *
     * @var array
     */
    public $request;

    /**
     * 查询参数
     *
     * @var array
     */
    protected $query;

    /**
     * 指定的其它属性
     *
     * @var array
     */
    protected $attributes;

    /**
     * 服务器信息
     *
     * @var array
     */
    protected $server;

    /**
     * 上传的文件
     *
     * @var array
     */
    protected $files;

    /**
     * 用户发送的 cookie
     *
     * @var array
     */
    protected $cookies;

    /**
     * 用户发送的头信息
     *
     * @var
     */
    public $headers;

    /**
     * 用户输入的内容
     *
     * @var null|string
     */
    protected $content;

    /**
     * Request constructor.
     *
     * @param array $query
     * @param array $request
     * @param array $attributes
     * @param array $cookies
     * @param array $files
     * @param array $server
     * @param null $content
     */
    public function __construct(array $query = [], array $request = [], array $attributes = [], array $cookies = [], array $files = [], array $server = [], $content = null)
    {
        if (empty($request) && in_array($this->getMethod(), ['PUT', 'DELETE', 'PATCH'])) {
            if (0 === strpos($_SERVER['CONTENT_TYPE'], 'application/x-www-form-urlencoded')) {
                $content = file_get_contents('php://input');
                parse_str($content, $request);
            }
        }

        if (empty($request) && is_int(stripos($_SERVER['CONTENT_TYPE'], 'application/json'))) {
            $request = (array)json_decode(file_get_contents("php://input"), true);
        }

        $this->request = $request;
        $this->query = $query;
        $this->attributes = $attributes;
        $this->input = array_merge($request, $query, $attributes);
        $this->cookies = $cookies;
        $this->files = $files;
        $this->server = $server;

        $this->content = $content;
        $this->pathInfo = null;
        $this->requestUri = null;
        $this->baseUrl = null;
        $this->basePath = null;
        $this->method = $this->getMethod();
        $this->format = null;
    }

    static public function createFromGlobals()
    {
        $request = new static($_GET, $_POST, [], $_COOKIE, $_FILES, $_SERVER);

        if (empty($request) && in_array($request->getMethod(), ['PUT', 'DELETE', 'PATCH'])) {
            if (0 === strpos($_SERVER['CONTENT_TYPE'], 'application/x-www-form-urlencoded')) {
                $content = file_get_contents('php://input');
                parse_str($content, $request);
                $request->request = $request;
            }
        }

        if (empty($request) && is_int(stripos($_SERVER['CONTENT_TYPE'], 'application/json'))) {
            $request->request = json_decode(file_get_contents("php://input"), true);
        }

        return $request;
    }

    /**
     * 获取全部请求输入
     *
     * @return mixed
     */
    public function input()
    {
        return $this->input;
    }

    /**
     * 获取用户输入, example: request('name', 'default_name'); request(['id', 'name'], ['name' => 'default_name'])
     *
     * @param $key
     * @param null $default
     *
     * @return mixed
     */
    public function get($key, $default = null)
    {
        if (is_array($key)) {
            $request = [];
            foreach ($key as $k) {
                $_default = is_array($default) && isset($default[$k]) ? $default[$k] : null;
                $request[$k] = isset($this->input[$k]) && $this->input[$k] !== null ? $this->input[$k] : $_default;
            }
        }
        else {
            $request = isset($this->input[$key]) && $this->input[$key] !== null ? $this->input[$key] : $default;
        }

        return $request;
    }

    /**
     * 获取除了制定的键以外的全部输入
     *
     * @param array|mixed $keys
     *
     * @return array
     */
    public function except($keys)
    {
        $keys = is_array($keys) ? $keys : [$keys];
        $input = $this->input();

        foreach ($keys as $key) {
            if (array_key_exists($key, $input)) {
                unset($input[$key]);
            }
        }

        return $input;
    }

    /**
     * 获取全部用户输入
     * todo 包括上传的文件
     *
     * @return mixed
     */
    public function all()
    {
        return $this->input;
    }

    /**
     * 获取上传的文件
     * todo 暂时直接返回临时文件位置, 后面改成返回文件对象
     *
     * @param $key
     *
     * @return null
     */
    public function file($key)
    {
        return array_key_exists($key, $this->files) ? $this->files[$key]['tmp_name'] : null;
    }

    /**
     * 判断是否有上传文件
     *
     * @param $key
     *
     * @return bool
     */
    public function hasFile($key)
    {
        return array_key_exists($key, $this->files);
    }

    /**
     * 获取指定名称的 cookie
     *
     * @param $key
     * @param null $default
     *
     * @return mixed|null
     */
    public function cookie($key, $default = null)
    {
        return array_key_exists($key, $this->cookies) ? $this->cookies[$key] : $default;
    }

    /**
     * 返回 Request 全部 cookie
     *
     * @return array
     */
    public function cookies()
    {
        return $this->cookies;
    }

    /**
     * 检查  Request 中是否有某个名称的 cookie
     *
     * @param $key
     *
     * @return bool
     */
    public function hasCookie($key)
    {
        return array_key_exists($key, $this->cookies);
    }

    public function baseUrl()
    {
        return rtrim(app()->configGet('app.base_url'), '/');
    }

    /**
     * 获取当前 url, 不带参数
     *
     * @return string
     */
    public function url()
    {
        return $this->baseUrl(). $this->getPathInfo();
    }

    public function path()
    {
        //todo
    }

    public function ip()
    {
        // todo
    }

    public function uri()
    {
        $uri = urldecode($this->server('REQUEST_URI'));
        $uri = preg_replace('/\/{1,}/', '/', $uri);

        return $uri;
    }

    public function fullUri()
    {
        return ($this->server('HTTPS') ? 'https://' : 'http://') . $this->server('HTTP_HOST').$this->uri();
    }

    /**
     * 获取 pathInfo 目录
     *
     * @param int $index
     * @param null $default
     *
     * @return null
     */
    public function segment($index, $default = null)
    {
        return array_key_exists($index, $this->segments()) ? $this->segments()[$index] : $default;
    }

    /**
     * 获取 pathInfo 全部目录
     *
     * @return array
     */
    public function segments()
    {
        return explode('/', $this->getPathInfo());
    }

    public function header()
    {
        // todo
    }

    /**
     * 从 $_SERVER 中获取变量
     *
     * @param $key
     * @param null $default
     *
     * @return mixed|null
     */
    public function server($key, $default = null)
    {
        return array_key_exists($key, $this->server) ? $this->server[$key] : $default;
    }

    /**
     * 是否是 ajax 请求, 使用 X-Requested-With 头判断
     *
     * @return bool
     */
    public function ajax()
    {
        return isset($_SERVER['HTTP_X_REQUESTED_WITH']) &&
        strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == strtolower('XMLHttpRequest');
    }

    public function pjax()
    {
        // todo
    }

    /**
     * 获取请求方式
     *
     * @return string
     */
    public function method()
    {
        return $this->getMethod();
    }

    /**
     * 获取当前的 http 请求方式
     *
     * @return string
     */
    public function getMethod()
    {
        if (isset($_POST['_method']) && !empty($_POST['_method'])) {
            $method = $_POST['_method'];
        }
        elseif(php_sapi_name() === 'cli') {
            $method = 'CLI';
        }
        else {
            $method = $_SERVER['REQUEST_METHOD'];
        }

        return strtoupper($method);
    }

    /**
     * 判断是否是某种请求方式
     *
     * @param $method
     *
     * @return bool
     */
    public function isMethod($method)
    {
        return $this->method() == $method;
    }

    public function isJson()
    {
        // todo
    }

    /**
     * 获取当前 http 请求 pathinfo
     *
     * @return string
     */
    public function getPathInfo()
    {
        $query = $this->server('QUERY_STRING');

        return '/'.trim(str_replace('?'.$query, '', $this->uri()), '/');
    }

    /**
     * 获取用户输入的内容
     *
     * @return null
     */
    public function getContent()
    {
        /**
         * @link http://php.net/manual/zh/migration56.new-features.php#migration56.new-features.reusable-input
         */
        if (PHP_VERSION_ID < 50600 && false === $this->content) {
            throw new \LogicException('getContent() PHP 版本小于 5.6 时 不能重复获取用户输入内容.');
        }

        if (null === $this->content) {
            $this->content = file_get_contents('php://input');
        }

        return $this->content;
    }

    /**
     * 获取页面来源
     *
     * @param null $default
     *
     * @return mixed|null
     */
    public function referer($default = null)
    {
        return $this->server('HTTP_REFERER', $default);
    }

    /**
     * 检测当前请求是否是移动设备
     *
     * @return bool
     */
    public function isMobile()
    {
        $pattern = '/(iPad|iPhone|Android|Mobile|Meego|Nokia|Windows Phone|Silk|KFAPWI|RIM Tablet)/isU';
        $userAgent = $this->server('HTTP_USER_AGENT');

        if ($userAgent && preg_match($pattern, $userAgent)) {
            return true;
        }

        return false;
    }
}
