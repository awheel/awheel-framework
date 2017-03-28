<?php

namespace light\Exceptions;

use Exception;

/**
 * 路由未匹配异常
 *
 * @package light\Exceptions
 */
class NotFoundHttpException extends HttpException
{
    public function __construct($message = "", $code = 0, Exception $previous = null)
    {
        parent::__construct(404, $message, $previous, $code);
    }
}
