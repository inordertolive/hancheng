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
namespace app\operation\controller;

use app\common\controller\Common;


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
        if (config('system.deny_ie') && get_browser_type() == 'ie') {
            $this->redirect('admin/ie/index');
        }

        // 判断是否登录，并定义ID常量
        $uid = $this->isLogin();
        //因PHP7缓存问题 使用defined可能造成无法保存
        define('ACCOUNT_ID',$uid);

        // 设置分页参数
        $this->setPageParam();
        $this->assign([
            'socket' => config('socket')
        ]);

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
    final protected function isLogin()
    {
        // 判断是否登录
        if ($uid = operation_is_signin()) {
            // 已登录
            return $uid;
        } else {
            // 未登录
            $this->redirect('login/signin');
        }
    }
}
