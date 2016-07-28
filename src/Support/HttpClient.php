<?php

namespace light\Support;

use light\Http\Response;

/**
 * Http 客户端
 *
 * Class HttpClient
 */
class HttpClient
{
    /**
     * GET 请求
     *
     * @var
     */
    const METHOD_GET = 'GET';

    /**
     * POST 请求
     *
     * @var
     */
    const METHOD_POST = 'POST';

    /**
     * PUT 请求
     *
     * @var
     */
    const METHOD_PUT = 'PUT';

    /**
     * DELETE 请求
     *
     * @var
     */
    const METHOD_DELETE = 'DELETE';

    /**
     * 当前请求方式
     *
     * @var string
     */
    protected $method;

    /**
     * 需要请求的目标地址
     *
     * @var
     */
    protected $server;

    /**
     * 请求的路径
     *
     * @var
     */
    protected $path;

    /**
     * 请求超时时间, 单位 秒
     *
     * @var int
     */
    protected $timeout = 3;

    /**
     * 当请求的地址发送重定向时, 是否跟随
     *
     * @var bool
     */
    protected $followLocation = false;

    /**
     * 若请求的地址发生重定向, 切设定跟随时的最大跟随次数(发生多次重定向)
     *
     * @var int
     */
    protected $maxRedirects = 1;

    /**
     * 请求时需要同步发送的数据
     *
     * @var
     */
    protected $data = [];

    /**
     * 请求时附带的 cookie
     *
     * @var
     */
    protected $cookies;

    /**
     * 请求时附带的 header
     *
     * @var
     */
    protected $headers = [];

    /**
     * 返回
     *
     * @var Response
     */
    protected $response;

    /**
     * 初始化
     *
     * @param string $method
     * @param $server
     * @param array $params, 可选参数: timeout followLocation, header, cookie, data
     */
    public function __construct($method = self::METHOD_GET, $server = '', $params = [])
    {
        $this->setMethod($method);
        $this->setServer($server);

        if (!empty($params)) {
            foreach ($params as $key => $v) {
                $methodSet = 'set'.ucfirst($key);
                if (method_exists($this, $methodSet)) {
                    $this->$methodSet($v);
                }
            }
        }

        return $this;
    }

    /**
     * 设置超时时间
     *
     * @param $timeout
     *
     * @return $this
     */
    public function setTimeout($timeout)
    {
        $this->timeout = $timeout;
        return $this;
    }

    /**
     * 设置发生重定向时, 是否跟随
     *
     * @param bool $follow
     * @param int $maxRedirects
     *
     * @return $this
     */
    public function setFollowLocation($follow = false, $maxRedirects = 5)
    {
        if ($maxRedirects > 100) {
            $maxRedirects = 100;
        }

        $this->followLocation = $follow;
        $this->followLocation == true && $this->maxRedirects = $maxRedirects;

        return $this;
    }

    /**
     * 设置服务器
     *
     * @param $server
     *
     * @return $this
     */
    public function setServer($server)
    {
        $this->server = rtrim($server, '/');

        return $this;
    }

    /**
     * 设置请求的类型
     *
     * @param $method
     *
     * @return $this
     */
    public function setMethod($method)
    {
        $this->method = $method ? strtoupper($method) : self::METHOD_GET;

        return $this;
    }

    /**
     * 设置请求路径
     *
     * @param $path
     *
     * @return $this
     */
    public function setPath($path)
    {
        $this->path = '/' . ltrim($path, '/');

        return $this;
    }

    /**
     * 设置请求时需要发送的数据,
     *
     * @param $data
     *
     * @return $this
     */
    public function setData($data = [])
    {
        if (is_array($data)) {
            $this->data = $data;
        }

        return $this;
    }

    /**
     * 添加请求时需要发送的数据
     *
     * @param $key
     * @param $value
     *
     * @return $this
     */
    public function addData($key, $value)
    {
        $this->data = array_merge($this->data, [$key => $value]);

        return $this;
    }

    /**
     * 添加 header
     *
     * @param $header
     * @param $value
     *
     * @return $this
     */
    public function addHeader($header, $value)
    {
        $this->headers[$header] = $value;

        return $this;
    }

    /**
     * 设置 cookie
     *
     * @param $cookies
     *
     * @return $this
     */
    public function setCookies($cookies)
    {
        $this->cookies = $cookies;

        return $this;
    }

    /**
     * 添加 cookie
     *
     * @param $key
     * @param $value
     *
     * @return $this
     */
    public function addCookies($key, $value)
    {
        $this->cookies[$key] = $value;

        return $this;
    }

    /**
     * 获取全部配置, 后面可以细化成单独项目获取, 或使用魔术方法运行获取全部信息
     *
     * @return array
     */
    public function getConfig()
    {
        return [
            'headers' => $this->headers,
            'cookies' => $this->cookies,
            'method' => $this->method,
            'server' => $this->server,
        ];
    }

    /**
     * 发送请求
     *
     * @return Response
     */
    public function send()
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $this->method);
        curl_setopt($ch, CURLOPT_HEADER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, $this->followLocation);
        curl_setopt($ch, CURLOPT_MAXREDIRS, $this->maxRedirects);
        curl_setopt($ch, CURLOPT_TIMEOUT, $this->timeout);

        if (!empty($this->cookies)) {
            $cookie = '';
            foreach ($this->cookies as $key => $value) {
                $cookie .= "$key=$value; ";
            }

            curl_setopt($ch, CURLOPT_COOKIE, trim($cookie, '; '));
        }

        if (!empty($this->headers)) {
            $headers = [];
            foreach ($this->headers as $k => $v) {
                $headers[] = $k.': '.$v;
            }

            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            unset($headers);
        }

        $server = $this->server.$this->path;

        if (!empty($this->data)) {
            if (in_array($this->method, [self::METHOD_POST, self::METHOD_PUT, self::METHOD_DELETE])) {
                curl_setopt($ch, CURLOPT_POST, true);
                curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($this->data));
            }
            else {
                $httpQuery = http_build_query($this->data);
                if (is_int(stripos($this->server.$this->path, '?'))) {
                    $server = rtrim($server, '&').'&'.$httpQuery;
                }
                else {
                    $server .= '?'.$httpQuery;
                }
            }
        }

        curl_setopt($ch, CURLOPT_URL, $server);

        $content = curl_exec($ch);
        @list($_header, $content) = explode("\r\n\r\n", $content, 2);
        if (!$_header || !$content) {
            $this->response = new Response(curl_error($ch), 500, []);
            return $this->response;
        }

        $_header = explode("\r\n", $_header);
        $header = [];
        foreach ($_header as $key => $value) {
            if (is_int(stripos($value, 'HTTP/1'))) {
                $header['protocol'] = explode(' ', $value)[0];
                continue;
            }

            $value = explode(':', $value);
            $header[strtolower($value[0])] = trim($value[1]);
        }

        $header = array_merge($header, curl_getinfo($ch));
        curl_close($ch);

        $this->response = new Response($content, $header['http_code'], $header);
        return $this->response;
    }
}
