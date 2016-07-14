<?php

namespace light\Support;

use light\Component;
use light\Cache\RedisDriver;

class RedisComponent implements Component
{
    /**
     * 组件访问器
     *
     * @return mixed
     */
    public function getAccessor()
    {
        return 'redis';
    }

    /**
     * 组件注册方法
     *
     * @return mixed
     */
    public function register()
    {
        $redisConfig = app()->configGet('redis');
        if (!$redisConfig) return null;

        $instances = [];
        foreach ($redisConfig as $project => $config) {
            $instances[$project] = function () use ($config) {
                return (new RedisDriver($config))->redis;
            };
        }

        return $instances;
    }
}
