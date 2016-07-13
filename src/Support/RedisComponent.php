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
     * todo 所有的 redis 配置都放到 redis.php 不能太分散
     *
     * @return mixed
     */
    public function register()
    {
        $instances['cache'] = function () {
            $cacheConfig = app()->configGet('cache.config.redis');
            return (new RedisDriver($cacheConfig))->redis;
        };

        $chatConfig = app()->configGet('chat.chat');
        foreach ($chatConfig as $project => $config) {
            $instances['chat.'.$project] = function () use ($config) {
                return (new RedisDriver($config))->redis;
            };
        }

        $redisConfig = app()->configGet('redis');
        foreach ($redisConfig as $project => $config) {
            $instances[$project] = function () use ($config) {
                return (new RedisDriver($config))->redis;
            };
        }

        return $instances;
    }
}
