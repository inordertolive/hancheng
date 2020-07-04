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
namespace app\user\controller;

use think\Controller;

/**
 *分享控制器
 * @package app\User\controller
 */
class Menu extends Controller
{
    /**
     * 获取侧栏菜单
     * @param string $module_id 模块id
     * @param string $module 模型名
     * @param string $controller 控制器名
     * @author 似水星辰 [ 2630481389@qq.com ]
     * @return string
     */
    public function getSidebarMenu($module_id = '', $module = '', $controller = '')
    {
        $menus = \app\user\model\Menu::getSidebarMenu($module_id, $module, $controller);

        $output = '';
        foreach ($menus as $key => $menu) {
            if (!empty($menu['url_value'])) {
                $output = $menu['url_value'];
                break;
            }
            if (!empty($menu['child'])) {
                $output = $menu['child'][0]['url_value'];
                break;
            }
        }
        return $output;
    }
}