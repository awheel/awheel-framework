<?php

namespace awheel\Http;

use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request as SymfonyRequest;

/**
 * http 请求
 *
 * @package awheel
 */
class Request extends SymfonyRequest
{
    /**
     * 开启 conent_type=application/json 类型请求参数覆写
     *
     * 提示: 也可以直接使用 $this->getContent() 方法获取 json 数据
     */
    public function enableHttpRequestParameterOverride()
    {
        if (is_int(stripos($_SERVER['CONTENT_TYPE'], 'application/json'))) {
            $content = (array)json_decode(file_get_contents("php://input"), true);
            $this->request->add($content);
        }
    }

    /**
     * 获取全部请求输入
     *
     * @return mixed
     */
    public function input()
    {
        return array_merge($this->query->all(), $this->attributes->all(), $this->request->all());
    }

    /**
     * 获取全部用户输入, 包括上传的文件
     *
     * @return mixed
     */
    public function all()
    {
        return array_replace_recursive($this->input(), $this->files->all());
    }

    /**
     * 获取上传的文件
     *
     * @param $key
     *
     * @return UploadedFile
     */
    public function file($key)
    {
        return $this->files->get($key);
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
        return $this->files->has($key);
    }

    /**
     * 获取指定名称的 cookie
     *
     * @param $key
     * @param null $default
     *
     * @return mixed
     */
    public function cookie($key, $default = null)
    {
        return $this->cookies->get($key, $default);
    }

    /**
     * 返回 Request 全部 cookie
     *
     * @return array
     */
    public function cookies()
    {
        return $this->cookies->all();
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
        return $this->cookies->has($key);
    }

    /**
     * @deprecated
     * @return string
     */
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
        return $this->getUriForPath($this->getPathInfo());
    }

    /**
     * 返回请求的 uri
     *
     * @return string
     */
    public function uri()
    {
        return $this->getRequestUri();
    }

    /**
     * 返回带完整的请求 uri
     *
     * @return string
     */
    public function fullUri()
    {
        return $this->getUri();
    }

    /**
     * 获取用户输入, example: request('name', 'default_name'); request(['id', 'name'], ['name' => 'default_name'])
     *
     * @param $key
     * @param $default
     * @param $deep
     *
     * @return mixed
     */
    public function get($key, $default = null, $deep = false)
    {
        if (is_array($key)) {
            $request = [];
            foreach ($key as $k) {
                $_default = is_array($default) && isset($default[$k]) ? $default[$k] : null;
                $request[$k] = parent::get($k, $_default);
            }
        }
        else {
            $request = parent::get($key, $default);
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
        $all = $this->all();

        foreach ($keys as $key) {
            if (array_key_exists($key, $all)) {
                unset($all[$key]);
            }
        }

        return $all;
    }

    /**
     * 返回客户端 ip
     *
     * @return string
     */
    public function ip()
    {
        return $this->getClientIp();
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

    /**
     * 获取请求的指定 header
     *
     * @param $key
     * @param null $default
     *
     * @return array|string
     */
    public function header($key, $default = null)
    {
        return $this->headers->get($key, $default);
    }

    /**
     * 获取请求的全部 header
     *
     * @return array
     */
    public function getHeaders()
    {
        return $this->headers->all();
    }

    /**
     * 从 $_SERVER 中获取变量
     *
     * @param $key
     * @param null $default
     *
     * @return mixed
     */
    public function server($key, $default = null)
    {
        return $this->server->get($key, $default);
    }

    /**
     * 是否是 ajax 请求, 使用 X-Requested-With 头判断
     *
     * @return bool
     */
    public function ajax()
    {
        return $this->isXmlHttpRequest();
    }

    /**
     * 是否是 pjax 请求, 使用 X-PJAX 头判断
     *
     * @return bool
     */
    public function pjax()
    {
        return $this->headers->get('X-PJAX') == true;
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
     * 判断是否是 json 请求
     *
     * @return bool
     */
    public function isJson()
    {
        $contentType = $this->headers->get('content-type');

        return strpos($contentType, '/json') !== false || strpos($contentType, '+json') !== false ;
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
        return $this->server->get('HTTP_REFERER', $default);
    }

    /**
     * 检测当前请求是否是移动设备
     *
     * @return bool
     */
    public function isMobile()
    {
        $pattern = '/(iPad|iPhone|Android|Mobile|Meego|Nokia|Windows Phone|Silk|KFAPWI|RIM Tablet)/isU';
        $userAgent = $this->server->get('HTTP_USER_AGENT');

        if ($userAgent && preg_match($pattern, $userAgent)) {
            return true;
        }

        return false;
    }

    /**
     * 判断是否支持 WebP
     *
     * @param string $param
     *
     * @return int
     */
    public function acceptWebP($param = 'accept_webp')
    {
        return is_int(stripos($this->server->get('HTTP_ACCEPT'), 'webp')) || (bool)$this->get($param) == true;
    }

    /**
     * 获取用户输入, 通过魔术方法代理: $request->get('name') => $request->name
     *
     * @param $name
     *
     * @return null
     */
    public function __get($name)
    {
        if (array_key_exists($name, $this->all())) {
            return $this->all()[$name];
        }

        return null;
    }
}
