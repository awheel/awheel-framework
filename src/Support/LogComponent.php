<?php

namespace light\Support;

use Monolog\Logger;
use light\Component;
use Monolog\Handler\StreamHandler;

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
        $file = app()->configGet('app.log_file', '/tmp/light.log');
        $level = app()->configGet('app.log_level', 'ERROR');

        return function () use ($file, $level) {
            $handler = new StreamHandler($file, $level);
            $handler->setFormatter(new HuputvFormatter());

            $log = new Logger(app()->name());
            $log->pushHandler($handler);

            return $log;
        };
    }
}
