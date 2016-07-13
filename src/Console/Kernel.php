<?php

namespace light\Console;

use light\App;
use Symfony\Component\Console\Application;

/**
 * 控制台 Kernel, 基于 Symfony Console
 *
 * @link https://symfony.com/doc/2.8/components/console/index.html
 *
 * @package light\Console
 */
class Kernel
{
    /**
     * @var App
     */
    protected $app;

    /**
     * @var Input
     */
    protected $input;

    /**
     * @var Output
     */
    protected $output;

    /**
     * Kernel constructor.
     *
     * @param App $app
     */
    public function __construct(App $app)
    {
        $this->app = $app;
    }

    /**
     * 处理 Console 请求
     *
     * @param Input $input
     * @param Output|null $output
     *
     * @return int
     */
    public function handle(Input $input, Output $output = null)
    {
        // 启动应用
        $this->app->bootstrap();

        // 注册 Input
        $this->app->register('input', $input);

        // 测试 Output
        $this->app->register('output', $output);

        // 获取命令列表
        $commands = require $this->app->basePath.'/bootstrap/commands.php';

        // 解析 运行 命令
        return $this->resolveCommands($commands)->run();
    }

    /**
     * 解析命令
     *
     * @param array $commands
     *
     * @return Application
     */
    public function resolveCommands($commands)
    {
        $consoleApp = new Application('HupuTv', App::VERSION);

        foreach ($commands as $command) {
            $consoleApp->add(new $command);
        }

        $this->app->register('consoleApp', $consoleApp);

        return $consoleApp;
    }

    /**
     * 结束
     *
     * @param Input $input
     * @param $status
     */
    public function terminate(Input $input, $status)
    {
        // todo 输出 状态码
    }
}
