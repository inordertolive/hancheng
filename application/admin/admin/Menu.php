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

use app\admin\model\Module as ModuleModel;
use app\admin\model\Menu as MenuModel;
use app\admin\model\Role as RoleModel;

/**
 * 菜单管理
 * @package app\admin\admin
 */
class Menu extends Base
{
    /**
     * 菜单首页
     * @param string $group 分组
     * @author 似水星辰 [ 2630481389@qq.com ]
     * @return mixed
     * @throws \Exception
     */
    public function index($group = 'admin')
    {
        // 保存模块排序
        if ($this->request->isPost()) {
            $modules = $this->request->post('sort/a');
            if ($modules) {
                $data = [];
                foreach ($modules as $key => $module) {
                    $data[] = [
                        'id'   => $module,
                        'sort' => $key + 1
                    ];
                }
                $MenuModel = new MenuModel();
                if (false !== $MenuModel->saveAll($data)) {
                    $this->success('保存成功');
                } else {
                    $this->error('保存失败');
                }
            }
        }

        cookie('__forward__', $_SERVER['REQUEST_URI']);
        // 配置分组信息
        $list_group = MenuModel::getGroup();
        foreach ($list_group as $key => $value) {
            $tab_list[$key]['title'] = $value;
            $tab_list[$key]['url']  = url('index', ['group' => $key]);
        }

        // 模块排序
        if ($group == 'module-sort') {
            $map['status'] = 1;
            $map['pid']    = 0;
            $modules = MenuModel::where($map)->order('sort,id')->column('icon,title', 'id');
            $this->assign('modules', $modules);
        } else {
            // 获取菜单数据
            $data_list = MenuModel::getMenusByGroup($group);

            $max_level = $this->request->get('max', 0);

            $this->assign('menus', $this->getNestMenu($data_list, $max_level));
        }

        $this->assign('tab_nav', ['tab_list' => $tab_list, 'active' => $group]);
        $this->assign('page_title', '菜单管理');
        return $this->fetch();
    }

    /**
     * 新增菜单
     * @param string $module 所属模块
     * @param string $pid 所属菜单id
     * @author 似水星辰 [ 2630481389@qq.com ]
     * @return mixed
     */
    public function add($module = 'admin', $pid = '')
    {
        // 保存数据
        if ($this->request->isPost()) {
            $data = $this->request->post('', null, 'trim');

            // 验证
            $result = $this->validate($data, 'Menu');
            // 验证失败 输出错误信息
            if(true !== $result) $this->error($result);

            // 顶部菜单url检查
//            if ($data['pid'] == 0 && $data['url_value'] == '') {
//                $this->error('顶级菜单的菜单链接不能为空');
//            }
			$MenuModel = new MenuModel;
            if ($menu = $MenuModel->allowField(true)->create($data)) {
                // 自动创建子菜单
                if ($data['auto_create'] == 1 && !empty($data['child_node'])) {
                    unset($data['icon'],$data['params']);
                    $this->createChildNode($data, $menu['id']);
                }
                // 添加角色权限
                if (isset($data['role'])) {
                    $this->setRoleMenu($menu['id'], $data['role']);
                }
                \Cache::clear();
                // 记录行为
                $details = '所属模块('.$data['module'].'),所属菜单ID('.$data['pid'].'),名称('.$data['title'].'),链接('.$data['url_value'].')';
                action_log('admin_menu_add', 'admin_menu', $menu['id'], UID, $details);
                $this->success('新增成功', cookie('__forward__'));
            } else {
                $this->error('新增失败');
            }
        }

		$fields =[
			['type' => 'hidden', 'name' => 'child_node' ,'value' => 'add,edit,delete,setstatus'],
			['type' => 'select', 'name' => 'module', 'title' => '所属模块', 'extra' =>  ModuleModel::getModule(), 'value' => $module, 'ajax_url' => url('menu/getModuleMenus')],
			['type' => 'select', 'name' => 'pid', 'title' => '所属菜单', 'extra' => MenuModel::getMenuTree(0, '', $module), 'value' => $pid ],
			[
				'type' => 'text', 
				'name' => 'title', 
				'title' => '菜单标题', 
				'attr' => 'data-rule="required;" data-msg-required="标题不能为空，可以使用中文或者英文"'
			],
            ['type' => 'text', 'name' => 'url_value', 'title' => '菜单链接' ,'可留空，如果是模块链接，请填写 "模块/控制器/操作"'],
			['type' => 'icon', 'name' => 'icon', 'title' => '菜单图标' ,'tips' => '例如： fa fa-fw fa-user'],
			['type' => 'radio', 'name' => 'auto_create', 'title' => '自动添加子菜单', 'extra' => ['否', '是'], 'value' => 0, 'tips' => '子菜单包含新增、编辑、设置状态、删除'],
			['type' => 'select', 'name' => 'role', 'title' => '角色', 'extra' => RoleModel::where('id', 'neq', 1)->column('id,name'), 'attr' => 'multiple'], 
            ['type' => 'radio', 'name' => 'online_hide', 'title' => '是否隐藏', 'extra' => ['否', '是'], 'value' => 0],
            ['type' => 'text', 'name' => 'sort', 'title' => '排序', 'value' => '99'],
            ['type' => 'radio', 'name' => 'status', 'title' => '状态', 'extra' => ['禁用', '启用'], 'value' => 1]
		];
		$this->assign('page_title','新增菜单');
		$this->assign('form_items', $fields);
        return $this->fetch('public/add');
    }

    /**
     * 编辑菜单
     * @param int $id 菜单ID
     * @author 似水星辰 [ 2630481389@qq.com ]
     * @return mixed
     */
    public function edit($id = 0)
    {
        if ($id === 0) $this->error('缺少参数');

        // 保存数据
        if ($this->request->isPost()) {
            $data = $this->request->post('', null, 'trim');

            // 验证
            $result = $this->validate($data, 'Menu');
            // 验证失败 输出错误信息
            if(true !== $result) $this->error($result);

            // 顶部菜单url检查
//            if ($data['pid'] == 0 && $data['url_value'] == '') {
//                $this->error('顶级菜单的菜单链接不能为空');
//            }

            // 设置角色权限
            $this->setRoleMenu($data['id'], isset($data['role']) ? $data['role'] : []);

            if (MenuModel::update($data)) {
                \Cache::clear();
                // 记录行为
                $details = '菜单ID('.$id.')';
                action_log('admin_menu_edit', 'admin_menu', $id, UID, $details);
                $this->success('编辑成功', cookie('__forward__'));
            } else {
                $this->error('编辑失败');
            }
        }

        // 获取数据
        $info = MenuModel::get($id);
        // 拥有该菜单权限的角色
        $info['role'] = RoleModel::getRoleWithMenu($id);

		$fields =[
			['type' => 'hidden', 'name' => 'id' ],
			['type' => 'linkage', 'name' => 'module', 'title' => '所属模块', 'extra' =>  ModuleModel::getModule(), 'ajax_url' => url('menu/getModuleMenus'), 'next_items' => 'pid'],
			['type' => 'select', 'name' => 'pid', 'title' => '所属菜单', 'extra' => MenuModel::getMenuTree(0, '', $info['module'])],    
			[
				'type' => 'text', 
				'name' => 'title', 
				'title' => '菜单标题', 
				'attr' => 'data-rule="required;" data-msg-required="标题不能为空，可以使用中文或者英文"'
			],
            ['type' => 'text', 'name' => 'url_value', 'title' => '菜单链接' ,'tips' => '可留空，如果是模块链接，请填写 "模块/控制器/操作"'],
			['type' => 'icon', 'name' => 'icon', 'title' => '菜单图标' ,'tips' => '例如： fa fa-fw fa-user'],
			['type' => 'select', 'name' => 'role', 'title' => '角色', 'extra' => RoleModel::where('id', 'neq', 1)->column('id,name'), 'attr' => 'multiple'], 
            ['type' => 'radio', 'name' => 'online_hide', 'title' => '是否隐藏', 'extra' => ['否', '是']],
            ['type' => 'text', 'name' => 'sort', 'title' => '排序'],
            ['type' => 'radio', 'name' => 'status', 'title' => '状态', 'extra' => ['禁用', '启用'], 'value' => 1]
		];
		$this->assign('page_title','编辑菜单');
		$this->assign('form_items', $this->setData($fields, $info));
        return $this->fetch('public/edit');
    }

    /**
     * 设置角色权限
     * @param string $role_id 角色id
     * @param array $roles 角色id
     * @author 似水星辰 [ 2630481389@qq.com ]
     * @throws \Exception
     */
    private function setRoleMenu($role_id = '', $roles = [])
    {
        $RoleModel = new RoleModel();

        // 该菜单的所有子菜单，包括本身菜单
        $menu_child   = MenuModel::getChildsId($role_id);
        $menu_child[] = (int)$role_id;
        // 该菜单的所有上下级菜单
        $menu_all = MenuModel::getLinkIds($role_id);
        $menu_all = array_map('strval', $menu_all);

        if (!empty($roles)) {
            // 拥有该菜单的所有角色id及菜单权限
            $role_menu_auth = RoleModel::getRoleWithMenu($role_id, true);
            // 已有该菜单权限的角色id
            $role_exists = array_keys($role_menu_auth);
            // 新菜单权限的角色
            $role_new = $roles;
            // 原有权限角色差集
            $role_diff = array_diff($role_exists, $role_new);
            // 新权限角色差集
            $role_diff_new = array_diff($role_new, $role_exists);
            // 新菜单角色权限
            $role_new_auth = RoleModel::getAuthWithRole($roles);

            // 删除原先角色的该菜单权限
            if ($role_diff) {
                $role_del_auth = [];
                foreach ($role_diff as $role) {
                    $auth     = json_decode($role_menu_auth[$role], true);
                    $auth_new = array_diff($auth, $menu_child);
                    $role_del_auth[] = [
                        'id'        => $role,
                        'menu_auth' => array_values($auth_new)
                    ];
                }
                if ($role_del_auth) {
                    $RoleModel->saveAll($role_del_auth);
                }
            }

            // 新增权限角色
            if ($role_diff_new) {
                $role_update_auth = [];
                foreach ($role_new_auth as $role => $auth) {
                    $auth = json_decode($auth, true);
                    if (in_array($role, $role_diff_new)) {
                        $auth = array_unique(array_merge($auth, $menu_all));
                    }
                    $role_update_auth[] = [
                        'id'        => $role,
                        'menu_auth' => array_values($auth)
                    ];
                }
                if ($role_update_auth) {
                    $RoleModel->saveAll($role_update_auth);
                }
            }
        } else {
            $role_menu_auth = RoleModel::getRoleWithMenu($role_id, true);
            $role_del_auth  = [];
            foreach ($role_menu_auth as $role => $auth) {
                $auth     = json_decode($auth, true);
                $auth_new = array_diff($auth, $menu_child);
                $role_del_auth[] = [
                    'id'        => $role,
                    'menu_auth' => array_values($auth_new)
                ];
            }
            if ($role_del_auth) {
                $RoleModel->saveAll($role_del_auth);
            }
        }
    }

    /**
     * 保存菜单排序
     * @author 似水星辰 [ 2630481389@qq.com ]
     */
    public function save()
    {
        if ($this->request->isPost()) {
            $data = $this->request->post();
            if (!empty($data)) {
                $menus = $this->parseMenu($data['menus']);
                foreach ($menus as $menu) {
                    if ($menu['pid'] == 0) {
                        continue;
                    }
                    MenuModel::update($menu);
                }
                \Cache::clear();
                $this->success('保存成功');
            } else {
                $this->error('没有需要保存的菜单');
            }
        }
        $this->error('非法请求');
    }

    /**
     * 添加子菜单
     * @param array $data 菜单数据
     * @param string $pid 上级菜单id
     * @author 似水星辰 [ 2630481389@qq.com ]
     */
    private function createChildNode($data = [], $pid = '')
    {
        unset($data['__token__']);
        $url_value  = substr($data['url_value'], 0, strrpos($data['url_value'], '/')).'/';
        $child_node = [];
        $data['pid'] = $pid;
		$menus = explode(',', $data['child_node']);
        foreach ($menus as $item) {
            switch ($item) {
                case 'add':
                    $data['title'] = '新增';
                    break;
                case 'edit':
                    $data['title'] = '编辑';
                    break;
                case 'delete':
                    $data['title'] = '删除';
                    break;
                case 'setstatus':
                    $data['title'] = '设置状态';
                    break;
            }
            $data['url_value']   = $url_value.$item;
			$data['sort'] = 100;
            $data['create_time'] = $this->request->time();
            $data['update_time'] = $this->request->time();
			unset($data['child_node'], $data['auto_create'], $data['role']);
            $child_node[] = $data;
        }

        if ($child_node) {
            $MenuModel = new MenuModel();
            $MenuModel->insertAll($child_node);
        }
    }

    /**
     * 递归解析菜单
     * @param array $menus 菜单数据
     * @param int $pid 上级菜单id
     * @author 似水星辰 [ 2630481389@qq.com ]
     * @return array
     */
    private function parseMenu($menus = [], $pid = 0)
    {
        $sort   = 1;
        $result = [];
        foreach ($menus as $menu) {
            $result[] = [
                'id'   => (int)$menu['id'],
                'pid'  => (int)$pid,
                'sort' => $sort,
            ];
            if (isset($menu['children'])) {
                $result = array_merge($result, $this->parseMenu($menu['children'], $menu['id']));
            }
            $sort ++;
        }
        return $result;
    }

    /**
     * 获取嵌套式菜单
     * @param array $lists 原始菜单数组
     * @param int $pid 父级id
     * @param int $max_level 最多返回多少层，0为不限制
     * @param int $curr_level 当前层数
     * @author 似水星辰 [ 2630481389@qq.com ]
     * @return string
     */
    public function getNestMenu($lists = [], $max_level = 0, $pid = 0, $curr_level = 1)
    {
        $result = '';
        foreach ($lists as $key => $value) {
            if ($value['pid'] == $pid) {
                $disable  = $value['status'] == 0 ? 'dd-disable' : '';

                // 组合菜单
                $result .= '<li class="dd-item dd3-item '.$disable.'" data-id="'.$value['id'].'">';
                $result .= '<div class="dd-handle dd3-handle">拖拽</div><div class="dd3-content"><i class="'.$value['icon'].'"></i> '.$value['title'];
                if ($value['url_value'] != '') {
                    $result .= '<span class="link"><i class="fa fa-link"></i> '.$value['url_value'].'</span>';
                }
                $result .= '<div class="action">';
                $result .= '<a href="'.url('add', ['module' => $value['module'], 'pid' => $value['id']]).'" class="btn btn-default btn-xs">新增</a> <a href="'.url('edit', ['id' => $value['id']]).'" data-original-title="编辑" class="btn btn-default btn-xs">编辑</a> ';
                if ($value['status'] == 0) {
                    // 启用
                    $result .= '<a href="'.url('setstatus',['ids' => $value['id'], 'type' => 'enable']).'" class="btn btn-default btn-xs ajax-get">启用</a> ';
                } else {
                    // 禁用
                    $result .= '<a href="'.url('setstatus',['ids' => $value['id'], 'type' => 'disable']).'" class="btn btn-default btn-xs ajax-get confirm">禁用</a> ';
                }
                $result .= '<a href="'.url('delete', ['ids' => $value['id']]).'" data-original-title="删除" class="btn btn-default btn-xs ajax-get confirm">删除</a></div>';
                $result .= '</div>';

                if ($max_level == 0 || $curr_level != $max_level) {
                    unset($lists[$key]);
                    // 下级菜单
                    $children = $this->getNestMenu($lists, $max_level, $value['id'], $curr_level + 1);
                    if ($children != '') {
                        $result .= '<ol class="dd-list">'.$children.'</ol>';
                    }
                }

                $result .= '</li>';
            }
        }
        return $result;
    }

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
        role_auth();
        $menus = MenuModel::getSidebarMenu($module_id, $module, $controller);

        $output = '';
        foreach ($menus as $key => $menu) {
            if (!empty($menu['url_value'])) {
                $output = $menu['url_value'];
                break;
            }
            if (!empty($menu['child'])) {
                $keys = array_keys($menu['child']);
                $output = $menu['child'][$keys[0]]['url_value'];
                break;
            }
        }
        return $output;
    }

    /**
     * 获取指定模块的菜单
     * @param string $module 模块名
     * @author 似水星辰 [ 2630481389@qq.com ]
     * @return \think\response\Json
     */
    public function getModuleMenus($module = '')
    {
        $menus = MenuModel::getMenuTree(0, '', $module);
        $result = [
            'code' => 1,
            'msg'  => '请求成功',
            'list' => format_linkage($menus)
        ];
        return json($result);
    }

	/**
     * 图标
     * @author 似水星辰 [ 2630481389@qq.com ]
     */

	public function icon_view(){
		return $this->fetch();
	}

    /**
     * 删除记录
     * @return void
     * @author 似水星辰 [ 2630481389@qq.com ]
     */
    public function delete(){
        $ids   = $this->request->isPost() ? input('post.ids/a') : input('param.ids');
        $ids   = (array)$ids;
        if($ids){
            $count = MenuModel::where('pid','in',$ids)->count();
            if($count){
                $this->error('请先删除子菜单');
            }
            $this->setStatus('delete');
        }

        $this->error('缺少参数');
    }

}
