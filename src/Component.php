<?php

namespace awheel;

/**
 * 组件接口, todo 组件可以设置实例化的环境: Http|Console|Both
 *
 * @package awheel
 */
interface Component
{
    /**
     * 组件访问器
     *
     * @return mixed
     */
    public function getAccessor();

    /**
     * 组件注册方法
     *
     * @return mixed
     */
    public function register();
}
