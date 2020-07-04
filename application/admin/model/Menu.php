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

namespace app\admin\model;

use app\admin\model\Role as RoleModel;
use think\Model as ThinkModel;
use think\Exception;
use service\Tree;

/**
 * 菜单模型
 * @package app\admin\model
 */
class Menu extends ThinkModel
{
    // 设置当前模型对应的完整数据表名称
    protected $table = '__ADMIN_MENU__';

    // 自动写入时间戳
    protected $autoWriteTimestamp = true;

    // 将菜单url转为小写
    public function setUrlValueAttr($value)
    {
        return strtolower(trim($value));
    }

    /**
     * 获取树形菜单
     * @param int $id 需要隐藏的菜单id
     * @param string $default 默认第一个菜单项，默认为“顶级菜单”，如果为false则不显示，也可传入其他名称
     * @param string $module 模型名
     * @author 似水星辰 [ 2630481389@qq.com ]
     * @return mixed
     */
    public static function getMenuTree($id = 0, $default = '', $module = '')
    {
        $result[0]       = '顶级菜单';
        $where[] = ['status' , 'egt', 0];
        if ($module != '') {
            $where1['module'] = $module;
        }

        // 排除指定菜单及其子菜单
        if ($id !== 0) {
            $hide_ids    = array_merge([$id], self::getChildsId($id));
            $where[] = ['id','not in', $hide_ids];
        }

        // 获取菜单
        $menus = Tree::toList(self::where($where)->where($where1)->order('pid,id')->column('id,pid,title'));
        foreach ($menus as $menu) {
            $result[$menu['id']] = $menu['title_display'];
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
     * 获取顶部菜单
     * @param string $max 最多返回多少个
     * @param string $cache_tag 缓存标签
     * @author 似水星辰 [ 2630481389@qq.com ]
     * @return array|mixed
     */
    public static function getTopMenu($max = '', $cache_tag = '')
    {
        $cache_tag .= '_role_'.session('admin_auth.role');
        $menus = cache($cache_tag);
        if (!$menus) {
            // 非开发模式，只显示可以显示的菜单
            if (config('develop_mode') == 0) {
                $map['online_hide'] = 0;
            }
            $map['status'] = 1;
            $map['pid']    = 0;
            $menus = self::where($map)->order('sort,id')->limit($max)->column('id,pid,module,title,url_value,url_target,icon,params');
            foreach ($menus as $key => &$menu) {
                // 没有访问权限的菜单不显示
                if (!RoleModel::checkAuth($menu['id'])) {
                    unset($menus[$key]);
                    continue;
                }
                if ($menu['url_value'] != '') {
                    $url = explode('/', $menu['url_value']);
                    $menu['controller'] = $url[1];
                    $menu['action']     = $url[2];
                    $menu['url_value']  = url($menu['url_value'], $menu['params']);
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
        $cache_tag  = strtolower('sidebar_menus_' . $module . '_' . $controller).'_role_'.session('admin_auth.role');
        $menus      = cache($cache_tag);

        if (!$menus) {
            // 获取当前菜单地址
            $location = self::getLocation($id);
            // 当前顶级菜单id
            $top_id = $location[0]['id'];
            // 获取顶级菜单下的所有菜单
            $map = [
                'status' => 1
            ];
            // 非开发模式，只显示可以显示的菜单
            if (config('develop_mode') == 0) {
                $map['online_hide'] = 0;
            }
            $menus = self::where($map)->order('sort,id')->column('id,pid,module,title,url_value,url_target,icon,params');

            // 解析模块链接
            foreach ($menus as $key => &$menu) {
                // 没有访问权限的菜单不显示
                if (!RoleModel::checkAuth($menu['id'])) {
                    unset($menus[$key]);
                    continue;
                }
                if ($menu['url_value'] != '') {
                    $menu['url_value'] = url($menu['url_value'], $menu['params']);
                }
            }
            $menus = Tree::toLayer($menus, $top_id, 2);

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
    public static function getLocation($id = '', $del_last_url = false, $check = true)
    {
        $model      = request()->module();
        $controller = request()->controller();
        $action     = request()->action();

        if ($id != '') {
            $cache_name = 'location_menu_'.$id;
        } else {
            $cache_name = 'location_'.$model.'_'.$controller.'_'.$action;
        }

        $location = cache($cache_name);

        if (!$location) {
            $map['url_value'] = strtolower($model.'/'.trim(preg_replace("/[A-Z]/", "_\\0", $controller), "_").'/'.$action);

            // 当前操作对应的菜单ID
            $curr_id  = $id == '' ? self::where($map)->where('pid','<>',0)->value('id') : $id;

            // 获取菜单ID是所有父级菜单
            $location = Tree::getParents(self::column('id,pid,title,url_value'), $curr_id);

            if ($check && empty($location)) {
                throw new Exception('找不到此菜单,可能未添加^^', 9001);
            }

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

    /**
     * 根据分组获取菜单
     * @param string $group 分组名称
     * @param bool|string $fields 要返回的字段
     * @param array $map 查找条件
     * @author 似水星辰 [ 2630481389@qq.com ]
     * @return array
     */
    public static function getMenusByGroup($group = '', $fields = true, $map = [])
    {
        $map['module'] = $group;
        return self::where($map)->order('sort,id')->column($fields, 'id');
    }

    /**
     * 获取菜单分组
     * @author 似水星辰 [ 2630481389@qq.com ]
     */
    public static function getGroup()
    {
        $map['status'] = 1;
        $map['pid']    = 0;
        $menus = self::where($map)->order('id,sort')->column('module,title');
        return $menus;
    }

    /**
     * 获取所有子菜单id
     * @param int $pid 父级id
     * @author 似水星辰 [ 2630481389@qq.com ]
     * @return array
     */
    public static function getChildsId($pid = 0)
    {
        $ids = self::where('pid', $pid)->column('id');
        foreach ($ids as $value) {
            $ids = array_merge($ids, self::getChildsId($value));
        }
        return $ids;
    }

    /**
     * 获取所有父菜单id
     * @param int $id 菜单id
     * @author 似水星辰 [ 2630481389@qq.com ]
     * @return array
     */
    public static function getParentsId($id = 0)
    {
        $pid  = self::where('id', $id)->value('pid');
        $pids = [];
        if ($pid != 0) {
            $pids[] = $pid;
            $pids = array_merge($pids, self::getParentsId($pid));
        }
        return $pids;
    }

    /**
     * 根据菜单id获取上下级的所有id
     * @param int $id 菜单id
     * @author 似水星辰 [ 2630481389@qq.com ]
     * @return array
     */
    public static function getLinkIds($id = 0)
    {
        $childs  = self::getChildsId($id);
        $parents = self::getParentsId($id);
        return array_merge((array)(int)$id, $childs, $parents);
    }
}
