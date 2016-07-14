<?php

namespace light\View;

use Smarty;

/**
 * View 层
 * todo 抽象, View 驱动化
 *
 * @package light
 */
class View
{
    /**
     * 驱动
     *
     * @var
     */
    protected $driver;

    /**
     * 配置
     *
     * @var array
     */
    protected $config = [];

    /**
     * 实例
     *
     * @var Smarty
     */
    protected $view;

    /**
     * 传递给 View 的数据
     *
     * @var array
     */
    protected $viewVars = [];

    /**
     * View 名称
     *
     * @var
     */
    protected $viewName;

    public function __construct($driver, $config)
    {
        $this->driver = $driver;
        $this->config = $config;

        switch ($this->driver) {
            // FIS
            case 'fis':
                $smarty = new Smarty();

                $smarty->error_reporting = error_reporting();

                $smarty->setTemplateDir($this->config['path'] . '/template');
                $smarty->setCompileDir($this->config['path'] . '/template_c');
                $smarty->setConfigDir($this->config['path'] . '/config');
                $smarty->setCacheDir($this->config['path'] . '/cache');
                $smarty->addPluginsDir($this->config['path'] . '/plugin');

                $smarty->left_delimiter = '{%';
                $smarty->right_delimiter = '%}';

                $this->view = $smarty;
                break;

            default:
                // nothing to do

                break;
        }

        return $this->view;
    }

    /**
     * 渲染
     *
     * @param $viewName
     * @param array $vars
     *
     * @return string
     */
    public function render($viewName, $vars = [])
    {
        $this->viewName = $viewName;
        $this->viewVars = array_merge($this->viewVars, (array) $vars);

        switch ($this->driver) {
            case 'fis':
                $response = $this->loadSmarty();
                break;

            default;
                $response = $this->loadSinglePage();
        }

        return $response;
    }

    /**
     * 给模板赋值
     *
     * @param $key
     * @param $value
     *
     * @return bool
     */
    public function assign($key, $value)
    {
        $this->viewVars = array_merge($this->viewVars, [$key => $value]);

        return true;
    }

    /**
     * 加载 Smarty
     *
     * @return mixed
     */
    protected function loadSmarty()
    {
        foreach ($this->viewVars as $var => $val) {
            $this->view->assign($var, $val);
        }

        $viewName = strtr($this->viewName, ['.' => '/']);

        ob_start();
        $this->view->display($viewName . '.tpl');
        $content = ob_get_contents();
        ob_end_clean();

        return $content;
    }

    /**
     * 加载 ReactJS, Angular 类单页面应用
     *
     * @return mixed
     */
    public function loadSinglePage()
    {
        extract($this->viewVars);
        ob_start();
        require rtrim($this->config['path'], '/') . '/' . $this->viewName;
        $content = ob_get_contents();
        ob_end_clean();

        return $content;
    }

    /**
     * 获取当前 View 驱动
     *
     * @return mixed
     */
    protected function getDriver()
    {
        return $this->driver;
    }

    /**
     * 获取当前 View 配置
     *
     * @return array
     */
    protected function getConfig()
    {
        return $this->config;
    }
}
