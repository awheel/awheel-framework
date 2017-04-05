<?php

namespace awheel\Support;

use Monolog\Logger;
use awheel\Component;
use Monolog\Handler\StreamHandler;
use Monolog\Handler\HandlerInterface;

/**
 * Log 组件
 *
 * @package Library
 */
class LogComponent implements Component
{
    /**
     * 组件访问器
     *
     * @return mixed
     */
    public function getAccessor()
    {
        return 'log';
    }

    /**
     * 组件注册方法
     *
     * @return mixed
     */
    public function register()
    {
        return function () {
            $file = app()->configGet('app.log_file', '/tmp/awheel.log');
            $level = app()->configGet('app.log_level', 'ERROR');
            $handler = app()->configGet('app.log_handler');

            if (!$handler || ! $handler instanceof HandlerInterface) {
                $handler = new StreamHandler($file, $level);
            }

            $log = new Logger(app()->name());
            $log->pushHandler($handler);

            return $log;
        };
    }
}
