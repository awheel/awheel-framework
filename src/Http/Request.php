<?php

namespace awheel\Http;

use Symfony\Component\HttpFoundation\Request as SymfonyRequest;

/**
 * http 请求
 *
 * @package awheel
 */
class Request extends SymfonyRequest
{
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
     * @return int
     */
    public function acceptWebP()
    {
        return is_int(stripos($this->server->get('HTTP_ACCEPT'), 'webp')) || (bool)$this->get('accept_webp') == true;
    }
}
