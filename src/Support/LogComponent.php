<?php

namespace light\Support;

use Monolog\Logger;
use light\Component;
use Monolog\Handler\StreamHandler;

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
     * todo 由配置文件制定 Handler
     *
     * @return mixed
     */
    public function register()
    {
        return function () {
            $file = app()->configGet('app.log_file', '/tmp/light.log');
            $level = app()->configGet('app.log_level', 'ERROR');

            $log = new Logger(app()->name());
            $log->pushHandler(new StreamHandler($file, $level));

            return $log;
        };
    }
}
