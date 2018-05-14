<?php

namespace awheel\Support;

use Monolog\Formatter\LineFormatter;
use Monolog\Logger;
use awheel\Component;
use Monolog\Handler\StreamHandler;
use Monolog\Handler\HandlerInterface;
use Monolog\Processor\MemoryUsageProcessor;

/**
 * Log 组件
 *
 * @package Library
 */
class LogComponent implements Component
{
    /**
     * @inheritdoc
     */
    public function getAccessor()
    {
        return 'log';
    }

    /**
     * @inheritdoc
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
            $format = app()->configGet('app.log.format');
            $date_format = app()->configGet('app.log.date_format');
            //set formatter
            $formatter = new LineFormatter($format,$date_format);
            $handler->setFormatter($formatter);
            $log = new Logger(app()->name());
            $log->pushHandler($handler);
            if(app()->configGet('app.log.show_memory_usage')){
                $log->pushProcessor(new MemoryUsageProcessor());
            }
            $log = new Logger(app()->name());
            $log->pushHandler($handler);

            return $log;
        };
    }
}
