<?php
// +----------------------------------------------------------------------
// | LwwanPHP[基于ThinkPHP5.1开发]
// +----------------------------------------------------------------------
// | Copyright (c) 2016-2019 http://www.lwwan.com
// +----------------------------------------------------------------------
// | Author 似水星辰 [ 2630481389@qq.com ]
// +----------------------------------------------------------------------
// | 星辰工作室 QQ群331378225
// +----------------------------------------------------------------------
namespace app\user\controller;

use app\common\controller\Common;
use app\user\model\Menu as MenuModel;


/**
 * 客服后台公共控制器
 * @package app\admin\controller
 */
class Admin extends Common
{
    /**
     * 初始化
     * @author 似水星辰 [2630481389@qq.com]
     */
    protected function initialize()
    {
        parent::initialize();
        // 是否拒绝ie浏览器访问

        // 判断是否登录，并定义ID常量
        $uid = $this->is_user_Login();
        //因PHP7缓存问题 使用defined可能造成无法保存
        define('USER_ID',$uid);

        if (!$this->request->isAjax()) {
            // 读取顶部菜单
            $this->assign('topMenus', MenuModel::getTopMenu(config('top_menu_max'), 'topMenus'));
            // 获取侧边栏菜单
            $this->assign('sidebarMenus', MenuModel::getSidebarMenu());
            // 获取面包屑导航
            $this->assign('location', MenuModel::getLocation('', true));
        }

        // 会员信息
        $this->assign('uinfo',session('mall_user_auth'));

        // 设置分页参数
        $this->setPageParam();
    }

    /**
     * 设置分页参数
     * @author 似水星辰 [2630481389@qq.com]
     */
    final protected function setPageParam()
    {
        $list_rows = input('?param.list_rows') ? input('param.list_rows') : config('list_rows');
        config('paginate.list_rows', $list_rows);
        config('paginate.query', input('get.'));
    }

    /**
     * 检查是否登录，没有登录则跳转到登录页面
     * @author 似水星辰 [2630481389@qq.com]
     * @return int
     */
    final protected function is_user_Login()
    {
        // 判断是否登录
        if ($uid = user_is_signin()) {
            // 已登录
            return $uid;
        } else {
            // 未登录
            $this->redirect('user/login/signin');
        }
    }
}
