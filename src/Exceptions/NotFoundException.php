<?php

namespace awheel\Exceptions;

use Exception;

/**
 * 路由未匹配异常
 *
 * @package awheel\Exceptions
 */
class NotFoundException extends HttpException
{
    /**
     * NotFoundException constructor.
     *
     * @param string $message
     * @param int $code
     * @param Exception|null $previous
     */
    public function __construct($message = "", $code = 0, Exception $previous = null)
    {
        parent::__construct(404, $message, $previous, $code);
    }
}
