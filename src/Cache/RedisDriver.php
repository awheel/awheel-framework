<?php

namespace light\Cache;

use Redis;

class RedisDriver implements InterfaceDriver
{
    /**
     * @var Redis
     */
    public $redis;

    /**
     * 构造函数, 唯一的传入参数, 是用来初始化连接的.
     *
     * @param $config
     */
    public function __construct(array $config)
    {
        // todo 扩展没安装, 抛出异常

        $host = $config['host'];
        $port = isset($config['port']) ? $config['port'] : 6379;
        $timeout = isset($config['timeout']) ? $config['timeout'] : 5;
        $db = isset($config['db']) ? $config['db'] : 0;
        $password = isset($config['password']) ? $config['password'] : null;

        $this->redis = new Redis();
        $this->redis->connect($host, $port, $timeout);
        $this->redis->auth($password);
        $this->redis->select($db);

        return $this;
    }

    /**
     * 添加一个缓存, 只有当 key 对应的 value 没有设置时才进行设置, 否则返回 false
     *
     * @param $key
     * @param $value
     * @param $second
     *
     * @return mixed
     */
    public function add($key, $value, $second)
    {
        $this->redis->setnx($key, $value);
        return $this->redis->expire($key, $second);
    }

    /**
     * 设置一个缓存, 当 key 对应的 value 已经存在时, 会覆盖, 并更新过期时间
     *
     * @param $key
     * @param $value
     * @param $second
     *
     * @return mixed
     */
    public function set($key, $value, $second)
    {
        return $this->redis->setex($key, $second, $value);
    }

    /**
     * 获取一个缓存的 value
     *
     * @param $key
     * @param null $default
     *
     * @return mixed
     */
    public function get($key, $default = null)
    {
        return $this->redis->get($key);
    }

    /**
     * 拉取一个缓存 key 对应的 value, 拉取后对应的 value 会消失
     *
     * @param $key
     * @param $default
     *
     * @return mixed
     */
    public function pull($key, $default)
    {
        $value = $this->get($key, $default);

        $this->remove($key);

        return $value;
    }

    /**
     * 一次获取多个 key 对应的缓存 value
     *
     * @param $keys
     *
     * @return mixed
     */
    public function multiGet($keys)
    {
        return $this->redis->mget($keys);
    }

    /**
     * 移除一个缓存对应的 value
     *
     * @param $key
     *
     * @return mixed
     */
    public function remove($key)
    {
        return $this->redis->del($key);
    }

    /**
     * 一次移除多个缓存对应的 value
     *
     * @param $keys
     *
     * @return mixed
     */
    public function multiRemove($keys)
    {
        return $this->redis->del($keys);
    }

    /**
     * 判断一个缓存 key 是否有对应的 value
     *
     * @param $key
     *
     * @return bool
     */
    public function has($key)
    {
        return (bool) $this->redis->get($key);
    }

    /**
     * 永久的储存一个 key 对应的 value
     *
     * @param $key
     * @param $value
     *
     * @return mixed
     */
    public function forever($key, $value)
    {
        $this->redis->set($key, $value);
    }

    /**
     * 让缓存 key 对应的 value 数值自增
     *
     * @param $key
     * @param int $value
     *
     * @return mixed
     */
    public function increment($key, $value = 1)
    {
        return $this->redis->incrBy($key, $value);
    }

    /**
     * 让缓存 key 对应的 value 数值自减
     *
     * @param  $key
     * @param  $value
     *
     * @return mixed
     */
    public function decrement($key, $value = 1)
    {
        return $this->redis->decrBy($key, $value);
    }
}
