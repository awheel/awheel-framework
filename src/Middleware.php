<?php

namespace awheel;

use Closure;
use awheel\Http\Request;

/**
 * 中间件
 * todo 中间件分级别, 例如: App 层面, Kernel 层面, Router/Console 层面
 *
 * @package awheel
 */
abstract class Middleware
{
    /**
     * 中间件实际执行方法
     * todo 接受参数: handle => handle(Request $request, Closure $next, $a, $b), middle => ['example:a,b']
     *
     * @param Request $request
     * @param Closure $next
     *
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        // todo before

        $response = $next($request);

        // todo after

        return $response;
    }
}
