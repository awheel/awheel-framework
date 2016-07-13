<?php

namespace light\View;

use light\Component;

class ViewComponent implements Component
{
    /**
     * 组件访问器
     *
     * @return mixed
     */
    public function getAccessor()
    {
        return 'view';
    }

    /**
     * 组件注册方法
     *
     * @return mixed
     */
    public function register()
    {
        if (app()->runningInConsole()) return [];

        $frontendConfig = app()->configGet('frontend');
        $instances = [];
        foreach ($frontendConfig as $driver => $config) {
            $instances[$driver] = function () use ($driver, $config) {
                return new View($driver, $config);
            };
        }

        return $instances;
    }
}
