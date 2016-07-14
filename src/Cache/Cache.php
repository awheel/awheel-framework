<?php

namespace light\Cache;

use light\App;
use light\Cache\InterfaceDriver;

/**
 * 缓存封装, 实际存储, 需要另外封装驱动.
 * 仅封装针对缓存的常规逻辑, 如需使用对应驱动的高级特性, 请绕过, 直接实例化.
 *
 * Class Cache
 */
class Cache
{
    /**
     * 缓存驱动
     *
     * @var InterfaceDriver
     */
    protected $driver;

    /**
     * 配置
     *
     * @var array
     */
    protected $config = [];

    /**
     * 默认缓存时间
     *
     * @var
     */
    protected $default = 60;

    /**
     * 缓存的 key 前缀
     *
     * @var
     */
    protected $prefix;

    /**
     * 命名空间
     *
     * @var string
     */
    protected $namespace = 'light\\Cache\\';

    /**
     * Cache constructor.
     *
     * @param array $config
     */
    public function __construct(array $config)
    {
        $this->driver = $config['driver'];
        $this->prefix = $config['prefix'];
        $this->config = $config['config'][$this->driver];

        $cacheDiver = $this->namespace . ucfirst($this->driver) . 'Driver';
        $this->driver = new $cacheDiver($this->config);

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
        return $this->driver->add($this->key($key), $value, $second);
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
        return $this->driver->set($this->key($key), $value, $second);
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
        $value = $this->driver->get($this->key($key));
        $value = is_null($value) ? $default : $value;

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
        return $this->driver->multiGet($this->key($keys));
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
        return $this->driver->remove($this->key($key));
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
        return (bool)$this->get($this->key($key), false);
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
        $data = $this->get($this->key($key), $default);
        $this->remove($key);

        return $data;
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
        return $this->driver->forever($this->key($key), $value);
    }

    /**
     * 缓存的 key 处理: 加前缀. todo 其它检查
     *
     * @param $key
     *
     * @return array|string
     */
    protected function key($key)
    {
        if (is_array($key)) {
            foreach ($key as $k => $v) {
                $key[$k] = $this->prefix.$v;
            }
        }
        else {
            $key = $this->prefix.$key;

        }

        return $key;
    }

}
