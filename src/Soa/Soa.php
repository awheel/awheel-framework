<?php

namespace light\Soa;

use light\App;
use Hupu\NetClient\NetClientTCP;

/**
 * Huputv Soa
 *
 * @package light
 */
class Soa
{
    /**
     * 客户端实例
     *
     * @var \Hupu\NetClient\NetClientTCP
     */
    protected $client;

    /**
     * Soa constructor.
     *
     * @param $config
     */
    public function __construct($config = [])
    {
        if ($this->client == null) {
            $client = new NetClientTCP();
            $client->try_reconnect = true;

            $state = $client->connect($config['host'], $config['port'], $config['timeout']);
            $state && $this->client = $client;
        }

        return null;
    }

    /**
     * 发送请求 && 接收数据
     *
     * @param $router
     * @param array $params
     *
     * @return mixed
     */
    public function request($router, $params = [])
    {
        $params = array_merge(['router' => $router], $params);
        $state = $this->client->send(json_encode($params)."HUPU-SVR");
        if (!$state) {
            app()->make('log')->error('soa send error', [$params]);
        }

        $data = json_decode($this->client->complete_recv(65535, 0, "HUPU-SVR"), true);
        if ($data == null) {
            app()->make('log')->error('soa receive error', [$params]);
        }

        return $data;
    }

    /**
     * 关闭 socket
     */
    public function __destruct()
    {
        $this->client->close();
    }
}
