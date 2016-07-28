<?php

namespace light\Console;

use Symfony\Component\Console\Style\OutputStyle;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Command\Command as SymfonyCommand;

abstract class Command extends SymfonyCommand
{
    /**
     * 命令名称
     *
     * @var
     */
    protected $name;

    /**
     * 命令说明
     *
     * @var
     */
    protected $description;

    /**
     * 控制台输入
     *
     * @var InputInterface
     */
    protected $input;

    /**
     * 控制台输出, 带 Style
     *
     * @var OutputStyle
     */
    protected $output;

    /**
     * 配置
     */
    public function configure()
    {
        $this->setName($this->name)->setDescription($this->description);

        $this->specifyParameters();
    }

    /**
     * 运行 console
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     *
     * @return int
     * @throws \Symfony\Component\Console\Exception\ExceptionInterface
     */
    public function run(InputInterface $input, OutputInterface $output)
    {
        $this->input = $input;

        $this->output = new SymfonyStyle($input, $output);

        return parent::run($input, $output);
    }

    /**
     * 执行命令
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     *
     * @return string
     */
    public function execute(InputInterface $input, OutputInterface $output)
    {
        return $this->handle();
    }

    /**
     *  HupuTv console 下命令实际处理方法
     *
     * @return string
     */
    public function handle()
    {
        return 'Hi Light';
    }

    /**
     * 设置命令行的参数和可选项
     *
     * @return void
     */
    protected function specifyParameters()
    {
        foreach ($this->getArguments() as $arguments) {
            call_user_func_array([$this, 'addArgument'], $arguments);
        }

        foreach ($this->getOptions() as $options) {
            call_user_func_array([$this, 'addOption'], $options);
        }
    }

    /**
     * 设置命令行的参数
     *
     * @return array
     */
    public function getArguments()
    {
        return [];
    }

    /**
     * 设置命令行的可选项
     *
     * @return array
     */
    public function getOptions()
    {
        return [];
    }

    /**
     * 在命令行输出一条普通内容
     *
     * @param $message
     */
    public function normal($message)
    {
        $this->colorWrite($message, null);
    }

    /**
     * 在命令行输出一条 Info 级别的内容
     *
     * @param $message
     */
    public function info($message)
    {
        $this->colorWrite($message, 'info');
    }

    /**
     * 在命令行输出一条 comment 级别的内容
     *
     * @param $message
     */
    public function comment($message)
    {
        $this->colorWrite($message, 'comment');
    }

    /**
     * 在命令行输出一条 question 级别的内容
     *
     * @param $message
     */
    public function question($message)
    {
        $this->colorWrite($message, 'question');
    }

    /**
     * 在命令行输出一条 error 级别的内容
     *
     * @param $message
     */
    public function error($message)
    {
        $this->colorWrite($message, 'error');
    }

    /**
     * 在命令行输出一条 confirm 级别的内容, 需要有默认值
     *
     * @param $question
     * @param $default
     *
     * @return bool
     */
    public function confirm($question, $default)
    {
        return $this->output->confirm($question, $default);
    }

    /**
     * 在命令行输出带颜色的内容
     *
     * @param $message
     * @param null $style
     */
    public function colorWrite($message, $style = null)
    {
        $this->output->writeln($style ? "<$style>$message</$style>" : $message);
    }
}
