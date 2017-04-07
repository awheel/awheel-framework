<?php

namespace awheel\Exceptions;

use Exception;

/**
 * 未授权的访问异常
 *
 * @package awheel\Exceptions
 */
class UnauthorizedException extends HttpException
{
    public function __construct($message = "", $code = 0, Exception $previous = null)
    {
        $headers = array('WWW-Authenticate' => $message);

        parent::__construct(401, $message, $previous, $code, $headers);
    }
}
