<?php

namespace light\Soa;

use light\Component;

class SoaComponent implements Component
{
    /**
     * 组件访问器
     *
     * @return mixed
     */
    public function getAccessor()
    {
        return 'soa';
    }

    /**
     * 组件注册方法
     *
     * @return mixed
     */
    public function register()
    {
        $soaConfig = app()->configGet('soa');
        if (!$soaConfig) return null;

        $instance = [];
        foreach ($soaConfig as $project => $config) {
            $instance[$project] = function () use ($config) {
                return new Soa($config);
            };
        }

        return $instance;
    }
}
