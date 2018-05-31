<?php
/**
 * Created by PhpStorm.
 * User: yanghaonan
 * Date: 2018/5/7
 * Time: 19:41
 * 错误处理handler
 */
namespace awheel\Exceptions;
use awheel\Http\Response;

class ExceptionHandler{

    /**
     * 异常处理
     * @param $exception
     * @return
     * @throws \Exception
     */
    public static function handleException($exception)
    {
        /**
         * 自定义错误返回
         */
        if ($exception instanceof UserException) {
            return $exception->handle();
        }

        //去除handler注册防止循环调用
        restore_error_handler();
        restore_exception_handler();

        //处理错误 记录日志
        app('log')->error('runtime exception: '. $exception->getMessage(), [
                'code' => $exception->getCode(),
                'file' => $exception->getFile(),
                'line' => $exception->getLine(),
                'trace' => $exception->getTraceAsString()
            ]);
            if (app()->configGet('app.debug')) {
                throw new \Exception('Http Request handle error', $exception->getCode(), $exception);
            }
        $response = new Response('', is_subclass_of($exception, 'awheel\Exceptions\HttpException') ? $exception->getStatusCode() : $exception->getCode());
        return $response;
    }

    /**
     * Unregisters this error handler by restoring the PHP error and exception handlers.
     */
    public function unregister()
    {
        restore_error_handler();
        restore_exception_handler();
    }
}
