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

namespace app\common\behavior;

use app\admin\model\Config as ConfigModel;
use app\admin\model\Module as ModuleModel;
use think\facade\Env;

/**
 * 初始化配置信息行为
 * 将系统配置信息合并到本地配置
 * @package app\common\behavior
 * @author 似水星辰 [ 2630481389@qq.com ]
 */
class Config
{
    /**
     * 执行行为 run方法是Behavior唯一的接口
     * @access public
     * @return void
     */
    public function run()
    {
		// 获取当前模块名称
        $module = request()->module();

        //模板字符替换
        $viewReplaceStr = [
            // 静态资源目录
            '__STATIC__'    => '/static',
            // 文件上传目录
            '__UPLOADS__'   => '/uploads',
            // JS插件目录
            '__PLUG__'      => '/static/plugins',
            // 后台CSS目录
            '__ADMIN_CSS__' => '/static/admin/css',
            // 后台JS目录
            '__ADMIN_JS__'  => '/static/admin/js',
            // 后台IMG目录
            '__ADMIN_IMG__' => '/static/admin/images',
            // 模块view目录
            '__MODULE__' => '/static/'.$module,
        ];

        \View::config(['tpl_replace_string' => $viewReplaceStr]);

		// 安装操作直接return
        if (defined('INSTALL_ENTRANCE')) return;

        // 读取系统配置
        $system_config = cache('system_config');
        if (!$system_config) {
            $ConfigModel   = new ConfigModel();
            $system_config = $ConfigModel->getConfig();

            // 所有模型配置
            $module_config = ModuleModel::where('config', 'neq', '')->column('config', 'name');
            foreach ($module_config as $module_name => $config) {
                $system_config[strtolower($module_name).'_config'] = json_decode($config, true);
            }

            // 非开发模式，缓存系统配置
            if ($system_config['develop_mode'] == 0) {
                cache('system_config', $system_config);
            }
        }

        // 设置配置信息
        config($system_config,'app');

        // 如果定义了入口为admin，则修改默认的访问控制器层
        if(defined('ENTRANCE') && ENTRANCE == 'admin') {

            if ($module == 'index') {
                header('Location: '.url('admin/login/signin'));
                exit;
            }

            /*if ($module != '' && $module != 'admin' && !in_array($module, config('notread_module'))) {
                // 修改视图模板路径
                \View::config(['view_path' => 'theme/'. $module. '/admin/']);
            }

            if($module == 'admin'){
                \View::config(['view_path' => 'theme/admin/']);
            }*/

        } else {
            // 修改视图模板路径
            \View::config(['view_path' => Env::get('app_path'). $module. '/view/home/']);
        }
    }
}
