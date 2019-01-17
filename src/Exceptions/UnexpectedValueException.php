<?php

namespace awheel\Exceptions;

use Exception;

/**
 * 字符集异常
 *
 * @package awheel\Exceptions
 */
class UnexpectedValueException extends HttpException
{
    /**
     * UnexpectedValueException constructor.
     *
     * @param string $message
     * @param int $code
     * @param Exception|null $previous
     */
    public function __construct($message = "", $code = 0, Exception $previous = null)
    {
        parent::__construct(500, $message, $previous, $code);
    }
}
