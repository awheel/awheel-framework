<?php

namespace light\Exceptions;

use Exception;

/**
 * Http 异常
 *
 * @package light\Exceptions
 */
class HttpException extends Exception
{
    /**
     * Http 错误状态码
     *
     * @var int
     */
    protected $statusCode;

    /**
     * 发送给客户端的 header
     *
     * @var array
     */
    protected $headers;

    public function __construct($statusCode, $message = "", $code = 0, Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }

    /**
     * 获取 Http 错误状态码
     *
     * @return int
     */
    public function getStatusCode()
    {
        return $this->statusCode;
    }

    /**
     * 获取 Header
     *
     * @return array
     */
    public function getHeaders()
    {
        return $this->headers;
    }
}
