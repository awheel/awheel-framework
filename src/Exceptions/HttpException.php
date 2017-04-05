<?php

namespace awheel\Exceptions;

use Exception;

/**
 * Http 异常
 *
 * @package awheel\Exceptions
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

    /**
     * HttpException constructor.
     *
     * @param string $statusCode
     * @param string $message
     * @param Exception|null $previous
     * @param int $code
     * @param array $headers
     */
    public function __construct($statusCode, $message = "", $previous = null, $code = 0, $headers = [])
    {
        $this->statusCode = $statusCode;
        $this->headers = $headers;

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
