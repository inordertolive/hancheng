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

namespace app\admin\admin;

use app\common\controller\Common;
use app\admin\model\Menu as MenuModel;
use think\Db;

/**
 * 后台公共控制器
 * @package app\admin\controller
 */
class Base extends Common
{
    /**
     * 初始化
     * @author 似水星辰 [ 2630481389@qq.com ]
     */
    protected function initialize()
    {
        parent::initialize();
        // 判断是否登录，并定义用户ID常量
        defined('UID') or define('UID', $this->isLogin());
		// 如果不是ajax请求，则读取菜单
        if (!$this->request->isAjax()) {
            // 读取顶部菜单
            $this->assign('topMenus', MenuModel::getTopMenu(config('top_menu_max'), 'topMenus'));
            // 读取全部顶级菜单
            $this->assign('topMenusAll', MenuModel::getTopMenu('', 'topMenusAll'));
            // 获取侧边栏菜单
            $this->assign('sidebarMenus', MenuModel::getSidebarMenu());
            // 获取面包屑导航
            $this->assign('location', MenuModel::getLocation('', true));
        }
    }

    /**
     * 检查是否登录，没有登录则跳转到登录页面
     * @author 似水星辰 [ 2630481389@qq.com ]
     * @return int
     */
    final protected function isLogin()
    {
        // 判断是否登录
        if ($uid = is_signin()) {
            // 已登录
            return $uid;
        } else {
            // 未登录
            $this->redirect('admin/login/signin');
        }
    }
	/**
     * 清空缓存
     * @author 似水星辰 [ 2630481389@qq.com ]
     */
	final protected function clearCache(){
		if (!empty(config('cache_type'))) {
            foreach (config('cache_type') as $item) {
                if ($item == 'LOG_PATH') {
                    $dirs = (array) glob(constant($item) . '*');
                    foreach ($dirs as $dir) {
                        array_map('unlink', glob($dir . '/*.log'));
                    }
                    array_map('rmdir', $dirs);
                } else {
                    array_map('unlink', glob(constant($item) . '/*.*'));
                }
            }
            \Cache::clear();
			return true;
        }else {
            $this->error('请在系统设置中选择需要清除的缓存类型');
        }
	}

	/**
     * 获取当前操作模型
     * @author 似水星辰 [ 2630481389@qq.com ]
     * @return object|\think\db\Query
     */
    final protected function getCurrModel()
    {
        $table_token = input('param._t', '');
        $module      = $this->request->module();
        $controller  = parse_name($this->request->controller());
        if($table_token){
            !session('?'.$table_token) && $this->error('参数错误');
            $table_data = session($table_token);
            $table      = $table_data['table'];
        }else{
            $table = input('param.model');
            $table_data['prefix'] = 1;
            $table_data['module'] = input('param.module');
            $table_data['controller'] = input('param.controller');
        }

        $table == '' && $this->error('参数错误');

        $Model = null;
        if ($table_data['prefix'] == 2) {
            // 使用模型
            try {
                $Model = Loader::model($table);
            } catch (\Exception $e) {
                $this->error('找不到模型：'.$table);
            }
        } else {
            // 使用DB类
            $table == '' && $this->error('缺少表名');
            if ($table_data['module'] != $module || $table_data['controller'] != $controller) {
                $this->error('非法操作');
            }

            $Model = $table_data['prefix'] == 0 ? Db::table($table) : Db::name($table);
        }

        return $Model;
    }

	/**
     * 快速编辑
     * @param array $record 行为日志内容
     * @author 似水星辰 [ 2630481389@qq.com ]
     */
    public function quickEdit($record = [])
    {
        $field           = input('post.name', '');
        $value           = input('post.value', '');
        $type            = input('post.type', '');
        $id              = input('post.pk', '');
        $validate        = input('post.validate', '');
        $validate_fields = input('post.validate_fields', '');
        if(!$value){
            $this->error('更改的内容不能为空');
        }
        $field == '' && $this->error('缺少字段名');
        $id    == '' && $this->error('缺少主键值');

        $Model = $this->getCurrModel();

        $protect_table = [
            '__ADMIN_USER__',
            '__ADMIN_ROLE__',
            config('database.prefix').'admin_user',
            config('database.prefix').'admin_role',
        ];

        // 验证是否操作管理员
        if (in_array($Model->getTable(), $protect_table) && $id == 1) {
            $this->error('禁止操作超级管理员');
        }

        // 验证器
        if ($validate != '') {
            $validate_fields = array_flip(explode(',', $validate_fields));
            if (isset($validate_fields[$field])) {
                $result = $this->validate([$field => $value], $validate.'.'.$field);
                if (true !== $result) $this->error($result);
            }
        }

        switch ($type) {
            // 日期时间需要转为时间戳
            case 'combodate':
                $value = strtotime($value);
                break;
            // 开关
            case 'switch':
                $value = $value == 'true' ? 1 : 0;
                break;
            // 开关
            case 'password':
                $value = Hash::make((string)$value);
                break;
        }

        // 主键名
        $pk     = $Model->getPk();
        $result = $Model->where($pk, $id)->setField($field, $value);

        cache('hook_plugins', null);
        cache('system_config', null);
        cache('access_menus', null);
        if (false !== $result) {
            // 记录行为日志
            if (!empty($record)) {
                call_user_func_array('action_log', $record);
            }
            $this->success('操作成功');
        } else {
            $this->error('操作失败');
        }
    }
}