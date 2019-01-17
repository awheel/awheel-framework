<?php

namespace awheel;

use Closure;

/**
 * 管道
 *
 * @package awheel
 */
class Pipeline
{
    /**
     * @var
     */
    protected $passable;

    /**
     * @var array
     */
    protected $pipes = [];

    /**
     * 在管道中传递的对象, 通常是 Request
     *
     * @param $passable
     *
     * @return $this
     */
    public function send($passable)
    {
        $this->passable = $passable;

        return $this;
    }

    /**
     * 对象会穿过/经过的管道/中间件
     *
     * @param $pipes
     *
     * @return $this
     */
    public function through($pipes)
    {
        $this->pipes = is_array($pipes) ? $pipes : func_get_args();

        return $this;
    }

    /**
     * 运行管道
     *
     * @param Closure $destination
     *
     * @return mixed|Http\Response
     */
    public function then(Closure $destination)
    {
        // 第一个切片, 通常是待处理的 Request
        $firstSlice = function ($passable) use ($destination) {
            return call_user_func($destination, $passable);
        };

        // 要穿过的管道, 通常是中间件
        $pipes = array_reverse($this->pipes);

        // 使用 array_reduce 递归调用
        return call_user_func(array_reduce($pipes, $this->getSlice(), $firstSlice), $this->passable);
    }

    /**
     * 获取切片 从外往里的中间件 (through)
     *
     * @return Closure
     */
    public function getSlice()
    {
        return function ($stack, $pipe) {
            return function ($passable) use ($stack, $pipe) {
                // 管道是一个闭包, 直接调用
                if ($pipe instanceof Closure) {
                    return call_user_func($pipe, $passable, $stack);
                }
                elseif (!is_object($pipe)) {
                    $pipe = new $pipe;
                }

                // 传递给中间件的参数
                // todo 传递其它参数
                $parameters = [$passable, $stack];

                return call_user_func_array([$pipe, 'handle'], $parameters);
            };
        };
    }
}
