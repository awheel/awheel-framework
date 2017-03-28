<?php

namespace light\Http;

use ArrayAccess;
use JsonSerializable;

/**
 * http 响应
 *
 * @package light
 */
class Response
{
    /**
     * 返回的头
     *
     * @var array
     */
    protected $headers = [];

    /**
     * 返回的内容
     *
     * @var
     */
    protected $content;

    /**
     * 返回的状态
     *
     * @var
     */
    protected $statusCode;

    /**
     * 返回的 cookies
     *
     * @var array
     */
    protected $cookies = [];

    /**
     * 编码
     *
     * @var
     */
    protected $charset;

    /**
     * Response constructor.
     *
     * @param $content
     * @param $status
     * @param array $headers
     */
    public function __construct($content = '', $status = 200, $headers = [])
    {
        $this->setHeaders($headers);
        $this->setContent($content);
        $this->setStatusCode($status);
    }

    /**
     * 工厂模式获取 Response 实例
     *
     * @param string $content
     * @param int $status
     * @param array $headers
     *
     * @return Response
     */
    public static function create($content = '', $status = 200, $headers = [])
    {
        return new static($content, $status, $headers);
    }

    /**
     * 设置 body
     *
     * @param $content
     *
     * @return $this
     */
    public function setContent($content)
    {
        if (is_array($content) || $content instanceof ArrayAccess || $content instanceof JsonSerializable) {
            $this->addHeader('Content-Type', 'application/json');

            $content = json_encode($content, JSON_UNESCAPED_UNICODE);
        }

        $this->content = (string) $content;

        return $this;
    }

    /**
     * 设置 http code
     *
     * @param $code
     *
     * @return $this
     */
    public function setStatusCode($code)
    {
        $this->statusCode = (int) $code;

        // todo setStatusText

        return $this;
    }

    /**
     * 添加 header
     *
     * @param $key
     * @param $value
     *
     * @return $this
     */
    public function addHeader($key, $value)
    {
        $this->headers[$key] = $value;

        return $this;
    }

    /**
     * 设置 header
     *
     * @param array $headers
     *
     * @return $this
     */
    public function setHeaders(array $headers)
    {
        if (is_array($headers)) {
            $this->headers = array_merge($this->headers, $headers);
        }

        return $this;
    }

    /**
     * 设置编码
     *
     * @param $charset
     *
     * @return $this
     */
    public function setCharset($charset)
    {
        $this->charset = $charset;

        return $this;
    }

    /**
     * 设置响应类型
     *
     * @param $contentType
     */
    public function setContentType($contentType)
    {
        $this->headers['Content-Type'] = $contentType;
    }

    /**
     * 获取编码
     *
     * @return mixed
     */
    public function getCharset()
    {
        return $this->charset;
    }

    /**
     * 获取 header
     *
     * @param $name
     *
     * @return string
     */
    public function getHeader($name)
    {
        return array_key_exists($name, $this->headers) ? $this->headers[$name] : null;
    }

    /**
     * 获取全部 header
     *
     * @return array
     */
    public function getHeaders()
    {
        return $this->headers;
    }

    /**
     * 获取 http code
     *
     * @return mixed
     */
    public function getStatusCode()
    {
        return $this->statusCode;
    }

    /**
     * 获取返回的 body
     *
     * @return mixed
     */
    public function getContent()
    {
        return $this->content;
    }

    /**
     * 发送 http 头和内容
     *
     * @return $this
     */
    public function send()
    {
        return $this->sendHeader()->sendContent();
    }

    /**
     * 发送 http 头
     *
     * @return $this
     */
    public function sendHeader()
    {
        if (headers_sent()) {
            return $this;
        }

        $charset = $this->charset ?: 'utf-8';

        if (!array_key_exists('Content-Type', $this->headers)) {
            $this->addHeader('Content-Type', 'text/html; charset='.$charset);
        }
        elseif (false === stripos($this->headers['Content-Type'], 'charset'))  {
            $this->addHeader('Content-Type', $this->headers['Content-Type'].'; charset='.$charset);
        }

        foreach ($this->headers as $item => $value) {
            $values = is_array($value) ? $value : [$value];
            foreach($values as $_value){
                header("{$item}: {$_value}", false, $this->statusCode);
            }
        }

        // todo 设置 protocol, http/1.0, http/1.1, http/2

        // todo 发送 cookie

        return $this;
    }

    /**
     * 发送内容
     *
     * @return $this
     */
    public function sendContent()
    {
        echo $this->content;

        return $this;
    }
}
