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

use app\admin\model\Config as ConfigModel;
use service\Format;

/**
 * 系统配置控制器
 * @package app\admin\admin
 */
class Config extends Base
{
    /**
     * 配置首页
     * @param string $group 分组
     * @return mixed
     * @throws \think\exception\DbException
     * @author 似水星辰 [ 2630481389@qq.com ]
     */
    public function index($group = 'base')
    {
        cookie('__forward__', $_SERVER['REQUEST_URI']);

        // 配置分组信息
        $list_group = config('config_group');
        $tab_list = [];
        foreach ($list_group as $key => $value) {
            $tab_list[$key]['title'] = $value;
            $tab_list[$key]['url'] = url('index', ['group' => $key]);
        }

        // 查询
        $map['group'] = $group;

        // 数据列表
        $data_list = ConfigModel::where($map)->order('sort asc,id asc')->paginate();

        $fields = [
            ['name', '名称', 'text'],
            ['title', '标题', 'text.edit'],
            ['type', '类型', 'status', '', config('form_item_type'), 'text-center'],
            ['sort', '排序', 'text.edit'],
            ['status', '状态', 'status', '', ['禁用', '正常'], 'text-center'],
            ['right_button', '操作', 'btn', '', '', 'text-center']
        ];

        return Format::ins()//实例化
        ->addColumns($fields)//设置字段
        ->setTabNav($tab_list, $group)//设置TAB分组
        ->setTopButton(['title' => '新增配置', 'href' => ['add', ['group' => $group]], 'icon' => 'fa fa-plus pr5', 'class' => 'btn btn-sm mr5 btn-success btn-flat'])
            ->setRightButtons($this->right_button)
            ->setData($data_list)//设置数据
            ->fetch();//显示
    }

    /**
     * 新增配置项
     * @param string $group 分组
     * @return mixed
     * @author 似水星辰 [ 2630481389@qq.com ]
     */
    public function add($group = '')
    {
        // 保存数据
        if ($this->request->isPost()) {
            // 表单数据
            $data = $this->request->post();

            // 验证
            $result = $this->validate($data, 'Config');
            if (true !== $result) $this->error($result);


            if ($config = ConfigModel::create($data)) {
                cache('system_config', null);
                // 记录行为
                $details = '详情：分组(' . $data['group'] . ')、类型(' . $data['type'] . ')、标题(' . $data['title'] . ')、名称(' . $data['name'] . ')';
                action_log('admin_config_add', 'admin_config', $config['id'], UID, $details);
                $this->success('新增成功', cookie('__forward__'));
            } else {
                $this->error('新增失败');
            }
        }

        $fields = [
            [
                'type' => 'radio',
                'name' => 'group',
                'title' => '配置分组',
                'extra' => config('config_group'),
                'value' => $group,
            ],
            [
                'type' => 'select',
                'name' => 'type',
                'title' => '配置类型',
                'tips' => '',
                'extra' => config('form_item_type')
            ],
            [
                'type' => 'text',
                'name' => 'title',
                'title' => '配置标题',
                'attr' => 'data-rule="required;" data-msg-required="标题不能为空，可以使用中文或者英文"'
            ],
            [
                'type' => 'text',
                'name' => 'name',
                'title' => '配置标识',
                'attr' => 'data-rule="required;name;" data-rule-name="[/^[a-zA-Z][a-zA-Z0-9_]*$/, \'请输入正确的配置标识，只能使用英文和下划线，必须以英文字母开头\']" data-msg-required="配置标识不能为空"'
            ],
            ['type' => 'textarea', 'name' => 'value', 'title' => '配置值'],
            ['type' => 'textarea', 'name' => 'extra', 'title' => '配置项', 'tips' => '用于单选、多选、下拉、联动等类型'],
            ['type' => 'textarea', 'name' => 'tips', 'title' => '配置说明'],
            ['type' => 'radio', 'name' => 'status', 'title' => '状态', '', 'extra' => ['禁用', '启用'], 'value' => 1]
        ];
        $this->assign('page_title', '新增管理员');
        $this->assign('form_items', $fields);
        return $this->fetch('public/add');
    }

    /**
     * 编辑配置
     * @param int $id
     * @return mixed
     * @author 似水星辰 [ 2630481389@qq.com ]
     */
    public function edit($id = 0)
    {
        if ($id === 0) $this->error('参数错误');

        // 保存数据
        if ($this->request->isPost()) {
            // 表单数据
            $data = $this->request->post();

            // 验证
            $result = $this->validate($data, 'Config');
            if (true !== $result) $this->error($result);

            // 原配置内容
            $config = ConfigModel::where('id', $id)->find();

            if ($config = ConfigModel::update($data)) {
                cache('system_config', null);
                $details = '未改动前数据：分组(' . $config['group'] . ')、类型(' . $config['type'] . ')、标题(' . $config['title'] . ')、名称(' . $config['name'] . ')';
                // 记录行为
                action_log('admin_config_edit', 'admin_config', $config['id'], UID, $details);
                $this->success('编辑成功', cookie('__forward__'));
            } else {
                $this->error('编辑失败');
            }
        }

        // 获取数据
        $info = ConfigModel::get($id);
        $fields = [
            ['type' => 'hidden', 'name' => 'id',],
            ['type' => 'radio', 'name' => 'group', 'title' => '配置分组', 'extra' => config('config_group'), 'value' => $group,],
            ['type' => 'select', 'name' => 'type', 'title' => '配置类型', 'extra' => config('form_item_type')],
            [
                'type' => 'text',
                'name' => 'title',
                'title' => '配置标题',
                'attr' => 'data-rule="required;" data-msg-required="标题不能为空，可以使用中文或者英文"'],

            [
                'type' => 'text',
                'name' => 'name',
                'title' => '配置标识',
                'attr' => 'data-rule="required;name;" data-rule-name="[/^[a-zA-Z][a-zA-Z0-9_]*$/, \'请输入正确的配置标识，只能使用英文和下划线，必须以英文字母开头\']" data-msg-required="配置标识不能为空"'
            ],
            ['type' => 'textarea', 'name' => 'value', 'title' => '配置值'],
            ['type' => 'textarea', 'name' => 'extra', 'title' => '配置项', 'tips' => '用于单选、多选、下拉、联动等类型'],
            ['type' => 'textarea', 'name' => 'tips', 'title' => '配置说明'],
            ['type' => 'radio', 'name' => 'status', 'title' => '状态', '', 'extra' => ['禁用', '启用'], 'value' => 1]
        ];
        $this->assign('page_title', '编辑配置');
        $this->assign('form_items', $this->setData($fields, $info));
        return $this->fetch('public/edit');
    }
}