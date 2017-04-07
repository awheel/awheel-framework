<?php

namespace awheel\Exceptions;

use Exception;

/**
 * 无效请求异常
 *
 * @package awheel\Exceptions
 */
class BadRequestException extends HttpException
{
    /**
     * BadRequestException constructor.
     *
     * @param string $message
     * @param int $code
     * @param Exception|null $previous
     */
    public function __construct($message = "", $code = 0, Exception $previous = null)
    {
        parent::__construct(400, $message, $previous, $code);
    }
}
