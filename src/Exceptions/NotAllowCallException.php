<?php

namespace light\Exceptions;

use Exception;

/**
 * 请求方式不允许异常
 *
 * @package light\Exceptions
 */
class NotAllowCallException extends HttpException
{
    public function __construct($message = "", $code = 0, Exception $previous = null)
    {
        parent::__construct(405, $message, $previous, $code);
    }
}
