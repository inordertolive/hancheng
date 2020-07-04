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

namespace app\user\admin;

use app\admin\admin\Base;
use app\user\model\Menu as MenuModel;

/**
 * 会员菜单控制器
 * @package app\Menu\admin
 */
class Menu extends Base
{
    /**
     * 菜单首页
     * @return mixed
     * @throws \Exception
     * @author 似水星辰 [ 2630481389@qq.com ]
     */
    public function index()
    {
        cookie('__forward__', $_SERVER['REQUEST_URI']);
        $data_list = MenuModel::order('sort,aid')->column(true, 'aid');
        $max_level = $this->request->get('max', 0);
        $this->assign('menus', $this->getNestMenu($data_list, $max_level));
        $this->assign('page_title', '菜单管理');
        return $this->fetch();
    }

    /**
     * 新增
     * @return mixed
     * @author 似水星辰 [2630481389@qq.com]
     */
    public function add($pid = 0)
    {
        // 保存数据
        if ($this->request->isPost()) {
            // 表单数据
            $data = $this->request->post();

            // 验证
            $result = $this->validate($data, 'Menu.add');
            if (true !== $result) $this->error($result);

            if ($menu = MenuModel::create($data)) {
                // 自动创建子菜单
                if ($data['auto_create'] == 1 && !empty($data['child_node'])) {
                    unset($data['icon'], $data['params']);
                    $this->createChildNode($data, $menu['aid']);
                }
                $this->success('新增成功', cookie('__forward__'));
            } else {
                $this->error('新增失败');
            }
        }

        $fields = [
            ['type' => 'hidden', 'name' => 'child_node', 'value' => 'add,edit,delete,setstatus'],
            ['type' => 'select', 'name' => 'pid', 'title' => '上级菜单', 'tips' => '', 'extra' => MenuModel::getMenuTree(0), 'value' => $pid],
            ['type' => 'text', 'name' => 'title', 'title' => '菜单名称', 'tips' => '', 'attr' => '', 'value' => ''],
            ['type' => 'icon', 'name' => 'icon', 'title' => '图标', 'tips' => '', 'attr' => '', 'value' => ''],
            ['type' => 'text', 'name' => 'url_value', 'title' => '链接地址', 'tips' => '', 'attr' => '', 'value' => ''],
            ['type' => 'radio', 'name' => 'auto_create', 'title' => '自动添加子菜单', 'extra' => ['否', '是'], 'value' => 0, 'tips' => '子菜单包含新增、编辑、设置状态、删除'],
            ['type' => 'number', 'name' => 'sort', 'title' => '排序', 'tips' => '', 'attr' => '', 'value' => '0'],

        ];
        $this->assign('page_title', '新增会员菜单');
        $this->assign('form_items', $fields);
        return $this->fetch('../../admin/view/public/add');
    }

    /**
     * 编辑
     * @param null $id 会员菜单id
     * @return mixed
     * @author 似水星辰 [2630481389@qq.com]
     */
    public function edit($id = null)
    {
        if ($id === null) $this->error('缺少参数');

        // 保存数据
        if ($this->request->isPost()) {
            // 表单数据
            $data = $this->request->post();

            // 验证
            $result = $this->validate($data, 'Menu.edit');
            if (true !== $result) $this->error($result);

            if (MenuModel::update($data)) {
                $this->success('编辑成功', cookie('__forward__'));
            } else {
                $this->error('编辑失败');
            }
        }

        $info = MenuModel::get($id);
        $fields = [
            ['type' => 'hidden', 'name' => 'aid'],
            ['type' => 'select', 'name' => 'pid', 'title' => '上级菜单', 'tips' => '', 'extra' => MenuModel::getMenuTree()],
            ['type' => 'text', 'name' => 'title', 'title' => '菜单名称', 'tips' => '', 'attr' => ''],
            ['type' => 'icon', 'name' => 'icon', 'title' => '图标', 'tips' => '', 'attr' => ''],
            ['type' => 'text', 'name' => 'url_value', 'title' => '链接地址', 'tips' => '', 'attr' => ''],
            ['type' => 'number', 'name' => 'sort', 'title' => '排序', 'tips' => '', 'attr' => ''],

        ];
        $this->assign('page_title', '编辑会员菜单');
        $this->assign('form_items', $this->setData($fields, $info));
        return $this->fetch('admin@public/edit');
    }

    /**
     * 获取嵌套式菜单
     * @param array $lists 原始菜单数组
     * @param int $pid 父级id
     * @param int $max_level 最多返回多少层，0为不限制
     * @param int $curr_level 当前层数
     * @return string
     * @author 似水星辰 [ 2630481389@qq.com ]
     */
    public function getNestMenu($lists = [], $max_level = 0, $pid = 0, $curr_level = 1)
    {
        $result = '';
        foreach ($lists as $key => $value) {
            if ($value['pid'] == $pid) {
                $disable = $value['status'] == 0 ? 'dd-disable' : '';

                // 组合菜单
                $result .= '<li class="dd-item dd3-item ' . $disable . '" data-id="' . $value['aid'] . '">';
                $result .= '<div class="dd-handle dd3-handle">拖拽</div><div class="dd3-content"><i class="' . $value['icon'] . '"></i> ' . $value['title'];
                if ($value['url_value'] != '') {
                    $result .= '<span class="link"><i class="fa fa-link"></i> ' . $value['url_value'] . '</span>';
                }
                $result .= '<div class="action">';
                $result .= '<a href="' . url('add', ['module' => $value['module'], 'pid' => $value['aid']]) . '" class="btn btn-default btn-xs">新增</a> <a href="' . url('edit', ['id' => $value['aid']]) . '" data-original-title="编辑" class="btn btn-default btn-xs">编辑</a> ';
                if ($value['status'] == 0) {
                    // 启用
                    $result .= '<a href="' . url('setstatus', ['ids' => $value['aid'], 'type' => 'enable']) . '" class="btn btn-default btn-xs ajax-get">启用</a> ';
                } else {
                    // 禁用
                    $result .= '<a href="' . url('setstatus', ['ids' => $value['aid'], 'type' => 'disable']) . '" class="btn btn-default btn-xs ajax-get confirm">禁用</a> ';
                }
                $result .= '<a href="' . url('delete', ['ids' => $value['aid']]) . '" data-original-title="删除" class="btn btn-default btn-xs ajax-get confirm">删除</a></div>';
                $result .= '</div>';

                if ($max_level == 0 || $curr_level != $max_level) {
                    unset($lists[$key]);
                    // 下级菜单
                    $children = $this->getNestMenu($lists, $max_level, $value['aid'], $curr_level + 1);
                    if ($children != '') {
                        $result .= '<ol class="dd-list">' . $children . '</ol>';
                    }
                }

                $result .= '</li>';
            }
        }
        return $result;
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
        $url_value = substr($data['url_value'], 0, strrpos($data['url_value'], '/')) . '/';
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
            $data['url_value'] = $url_value . $item;
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
     * @return array
     * @author 似水星辰 [ 2630481389@qq.com ]
     */
    private function parseMenu($menus = [], $pid = 0)
    {
        $sort = 1;
        $result = [];
        foreach ($menus as $menu) {
            $result[] = [
                'id' => (int)$menu['id'],
                'pid' => (int)$pid,
                'sort' => $sort,
            ];
            if (isset($menu['children'])) {
                $result = array_merge($result, $this->parseMenu($menu['children'], $menu['id']));
            }
            $sort++;
        }
        return $result;
    }

    /**
     * 删除记录
     * @return void
     * @author 似水星辰 [ 2630481389@qq.com ]
     */
    public function delete()
    {
        $ids = $this->request->isPost() ? input('post.ids/a') : input('param.ids');
        $ids = (array)$ids;
        if ($ids) {
            $count = MenuModel::where('pid', 'in', $ids)->count();
            if ($count) {
                $this->error('请先删除子菜单');
            }
            $this->setStatus('delete');
        }

        $this->error('缺少参数');
    }
}