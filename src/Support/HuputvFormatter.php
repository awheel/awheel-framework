<?php

namespace light\Support;

use Monolog\Formatter\NormalizerFormatter;

/**
 * 虎扑 tv 定制 monolog 格式
 *
 * @package light
 * @deprecated
 */
class HuputvFormatter extends NormalizerFormatter
{
    public function __construct()
    {
        parent::__construct('Y-m-d\TH:i:s.uP');
    }

    public function format(array $record)
    {
        $message = [
            'vtm' => @$record['datetime']->getTimestamp(),
            'channel' => @$record['channel'],
            'level' => @$record['level_name'],
            'message' => @$record['message'],
            'context' => @$record['context'],
        ];

        return $this->toJson($message) . "\n";
    }
}
