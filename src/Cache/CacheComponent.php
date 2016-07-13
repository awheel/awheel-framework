<?php

namespace light\Cache;

use light\Component;

class CacheComponent implements Component
{
    /**
     * 组件访问器
     *
     * @return mixed
     */
    public function getAccessor()
    {
        return 'cache';
    }

    /**
     * 组件注册方法
     *
     * @return mixed
     */
    public function register()
    {
        $config = app()->configGet('cache.driver');

        return function () use ($config) {
            return new Cache($config);
        };
    }
}
