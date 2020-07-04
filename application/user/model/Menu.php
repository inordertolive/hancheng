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

namespace app\user\model;

use think\Model as ThinkModel;
use app\user\model\Role as RoleModel;
use service\Tree;

/**
 * 单页模型
 * @package app\user\model
 */
class Menu extends ThinkModel
{
    // 设置当前模型对应的完整数据表名称
    protected $table = '__USER_MENU__';
    protected $pk = "aid";

    // 自动写入时间戳
    protected $autoWriteTimestamp = true;

    /**
     * 获取树形菜单
     * @param int $id 需要隐藏的菜单id
     * @param string $default 默认第一个菜单项，默认为“顶级菜单”，如果为false则不显示，也可传入其他名称
     * @author 似水星辰 [ 2630481389@qq.com ]
     * @return mixed
     */
    public static function getMenuTree($id = 0, $default = '')
    {
        $result[0] = '顶级菜单';
        $where[] = ['status', 'egt', 0];

        // 排除指定菜单及其子菜单
        if ($id !== 0) {
            $hide_ids = array_merge([$id], self::getChildsId($id));
            $where[] = ['aid', 'not in', $hide_ids];
        }

        // 获取菜单
        $menus = Tree::config(['id'=>'aid'])->toList(self::where($where)->order('pid,aid')->column('aid,pid,title'));
        foreach ($menus as $menu) {
            $result[$menu['aid']] = $menu['title_display'];
        }

        // 设置默认菜单项标题
        if ($default != '') {
            $result[0] = $default;
        }

        // 隐藏默认菜单项
        if ($default === false) {
            unset($result[0]);
        }

        return $result;
    }

    /**
     * 获取所有子菜单id
     * @param int $pid 父级id
     * @author 似水星辰 [ 2630481389@qq.com ]
     * @return array
     */
    public static function getChildsId($pid = 0)
    {
        $ids = self::where('pid', $pid)->column('aid');
        foreach ($ids as $value) {
            $ids = array_merge($ids, self::getChildsId($value));
        }
        return $ids;
    }

    /**
     * 获取顶部菜单
     * @param string $max 最多返回多少个
     * @param string $cache_tag 缓存标签
     * @author 似水星辰 [ 2630481389@qq.com ]
     * @return array|mixed
     */
    public static function getTopMenu($max = '', $cache_tag = '')
    {
        $cache_tag .= 'user_menu_' . USER_ID;
        $menus = cache($cache_tag);
        if (!$menus) {

            $map['status'] = 1;
            $map['pid'] = 0;
            $menus = self::where($map)->order('sort,aid')->limit($max)->column('aid,pid,title,url_value,icon');
            foreach ($menus as $key => &$menu) {

                if ($menu['url_value'] != '') {
                    $url = explode('/', $menu['url_value']);
                    $menu['controller'] = $url[1];
                    $menu['action'] = $url[2];
                    $menu['url_value'] = url($menu['url_value']);
                }
            }
            // 非开发模式，缓存菜单
            if (config('develop_mode') == 0) {
                cache($cache_tag, $menus);
            }
        }
        return $menus;
    }

    /**
     * 获取侧栏菜单
     * @param string $id 模块id
     * @param string $module 模块名
     * @param string $controller 控制器名
     * @author 似水星辰 [ 2630481389@qq.com ]
     * @return array|mixed
     * @throws Exception
     */
    public static function getSidebarMenu($id = '', $module = '', $controller = '')
    {
        $module     = $module == '' ? request()->module() : $module;
        $controller = $controller == '' ? request()->controller() : $controller;
        $cache_tag  = strtolower('user_sidebar_menus_' . $module . '_' . $controller).'_role_'.session('mall_user_auth.user_auth');
        $menus = cache($cache_tag);
        if (!$menus) {
            // 获取当前菜单地址
            $location = self::getLocation();
            // 当前顶级菜单id
            $top_id = $location[0]['aid'];
            // 获取顶级菜单下的所有菜单
            $map = [
                'status' => 1
            ];

            $menus = self::where($map)->order('sort,aid')->column('aid,pid,title,url_value,icon');

            // 解析模块链接
            foreach ($menus as $key => &$menu) {
                // 没有访问权限的菜单不显示
                if (!RoleModel::checkAuth($menu['aid'])) {
                    unset($menus[$key]);
                    continue;
                }
                if ($menu['url_value'] != '') {
                    $menu['url_value'] = url($menu['url_value']);
                }
            }
            $menus = Tree::config(['id' => 'aid'])->toLayer($menus, $top_id, 2);

            // 非开发模式，缓存菜单
            if (config('develop_mode') == 0) {
                cache($cache_tag, $menus);
            }
        }
        return $menus;
    }

    /**
     * 获取指定菜单ID的位置
     * @param string $id 菜单id，如果没有指定，则取当前菜单id
     * @param bool $del_last_url 是否删除最后一个菜单的url地址
     * @param bool $check 检查菜单是否存在，不存在则抛出错误
     * @return array|mixed
     * @throws Exception
     * @author 似水星辰 [ 2630481389@qq.com ]
     */
    public static function getLocation($id = '', $del_last_url = false)
    {
        $model = request()->module();
        $controller = request()->controller();
        $action = request()->action();

        if ($id != '') {
            $cache_name = 'user_location_menu_' . $id;
        } else {
            $cache_name = 'user_location_' . $model . '_' . $controller . '_' . $action;
        }

        $location = cache($cache_name);

        if (!$location) {
            $map = [
                ['pid', '<>', 0],
                ['url_value', '=', strtolower($model.'/'.trim(preg_replace("/[A-Z]/", "_\\0", $controller), "_").'/'.$action)]
            ];

            // 当前操作对应的菜单ID
            $curr_id = $id == '' ? self::where($map)->value('aid') : $id;

            // 获取菜单ID是所有父级菜单
            $location = Tree::config(['id' => 'aid'])->getParents(self::column('aid,pid,title,url_value'), $curr_id);

            // 剔除最后一个菜单url
            if ($del_last_url) {
                $location[count($location) - 1]['url_value'] = '';
            }

            // 非开发模式，缓存菜单
            if (config('develop_mode') == 0) {
                cache($cache_name, $location);
            }
        }

        return $location;
    }

}