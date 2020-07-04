<?php
// +----------------------------------------------------------------------
// |ZBPHP[基于ThinkPHP5.1开发]
// +----------------------------------------------------------------------
// | Copyright (c) 2016-2019 http://www.benbenwangluo.com
// +----------------------------------------------------------------------
// | Author 似水星辰 [ 2630481389@qq.com ]
// +----------------------------------------------------------------------
// | 中犇软件 技术六部 出品
// +----------------------------------------------------------------------

namespace app\common\controller;

use think\Container;
use think\Exception;

/**
 * 插件类
 * @package app\common\controller
 * @author 似水星辰 [ 2630481389@qq.com ]
 */
abstract class Addons
{
    /**
     * @var null 视图实例对象
     */
    protected $view = null;

    /**
     * @var string 插件配置文件
     */
    public $config_file = '';

    /**
     * @var string 插件路径
     */
    public $plugin_path = '';

    /**
     * @var string 错误信息
     */
    protected $error = '';

    /**
     * 构造方法
     */
    public function __construct()
    {
        $this->view = Container::get('view');
        $this->plugin_path = ROOT_PATH . 'addons/'.$this->getName().'/';
        if (is_file($this->plugin_path.'config.php')) {
            $this->config_file = $this->plugin_path.'config.php';
        }
    }

    /**
     * 获取插件名称
     * @author 似水星辰 [ 2630481389@qq.com ]
     * @return string
     */
    final public function getName()
    {
        $class = get_class($this);
        return substr($class, strrpos($class, '\\') + 1);
    }

    /**
     * 显示方法
     * @param string $template 模板或直接解析内容
     * @param array $vars 模板输出变量
     * @param array $replace 替换内容
     * @param array $config 模板参数
     * @param bool $renderContent 是否渲染内容
     * @throws Exception
     * @author 似水星辰 [ 2630481389@qq.com ]
     */
    final protected function fetch($template = '', $vars = [], $replace = [], $config = [], $renderContent = false)
    {
        if ($template != '') {
            if (!is_file($template)) {
                $template = $this->plugin_path. 'view/'. $template . '.' . config('template.view_suffix');

                if (!is_file($template)) {
                    throw new Exception('模板不存在：'.$template, 5001);
                }
            }
            echo $this->view->engine->layout(false)->fetch($template, $vars, $replace, $config, $renderContent);
        }
    }

    /**
     * 模板变量赋值
     * @param string $name 要显示的模板变量
     * @param string $value 变量的值
     * @author 似水星辰 [ 2630481389@qq.com ]
     * @return $this
     */
    final protected function assign($name = '', $value='')
    {
        $this->view->engine->assign($name, $value);
        return $this;
    }

    /**
     * 获取插件配置值，先从数据库获取，如果没有则从插件配置文件获取
     * @param string $name 插件名称
     * @author 似水星辰 [ 2630481389@qq.com ]
     * @return array|mixed
     */
    final public function getConfigValue($name='')
    {
        static $_config = array();
        if(empty($name)){
            $name = $this->getName();
        }
        if(isset($_config[$name])){
            return $_config[$name];
        }

        $config = addons_config($name);

        if (!$config) {
            if ($this->config_file != '') {
                $file_config = include $this->config_file;
            }

            if (isset($file_config) && $file_config != '') {
                $config = addons_parse_config($file_config);
                $_config[$name] = $config;
            }
        }

        return $config;
    }

    /**
     * 获取错误信息
     * @author 似水星辰 [ 2630481389@qq.com ]
     * @return string
     */
    final public function getError()
    {
        return $this->error;
    }

    /**
     * 必须实现安装方法
     * @author 似水星辰 [ 2630481389@qq.com ]
     * @return mixed
     */
    abstract public function install();

    /**
     * 必须实现卸载方法
     * @author 似水星辰 [ 2630481389@qq.com ]
     * @return mixed
     */
    abstract public function uninstall();
}
