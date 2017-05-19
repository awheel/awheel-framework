<?php

namespace awheel;

use Closure;
use awheel\Http\Request;

/**
 * 中间件
 *
 * @package awheel
 */
abstract class Middleware
{
    /**
     * 中间件实际执行方法
     *
     * @param Request $request
     * @param Closure $next
     *
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        // pre-operation

        $response = $next($request);

        //post-operation

        return $response;
    }
}
