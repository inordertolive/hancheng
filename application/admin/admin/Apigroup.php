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

use app\admin\admin\Base;
use app\admin\model\Apigroup as ApigroupModel;
use service\Format;
use service\Tree;

/**
 * 接口分组控制器
 * @package app\Apigroup\admin
 */
class Apigroup extends Base
{
    /**
     * 接口分组列表
     * @return mixed
     * @author 似水星辰 [2630481389@qq.com]
     */
    public function index($module = "admin")
    {
        cookie('__forward__', $_SERVER['REQUEST_URI']);
        // 查询
        $map = $this->getMap();
        // 查询
        $map['module'] = $module;
        // 排序
        $order = $this->getOrder();
        // 配置分组信息
        $list_group =  \app\admin\model\Module::column('name,title');

        $tab_list = [];
        foreach ($list_group as $key => $value) {
            $tab_list[$key]['title'] = $value;
            $tab_list[$key]['url'] = url('index', ['module' => $key]);
        }
        // 数据列表
        $data_list = ApigroupModel::where($map)->order($order)->paginate();
        $fields = [
            ['aid', 'ID'],
            ['name', '分组名称'],
            ['module', '所属模块','','',\app\admin\model\Module::column('name,title')],
            ['right_button', '操作', 'btn', '', '', 'text-center']
        ];
        return Format::ins()//实例化
        ->setPrimaryKey('aid')
            ->setTabNav($tab_list, $module)//设置TAB分组
            ->addColumns($fields)//设置字段
            ->setTopButtons($this->top_button)
            ->setRightButtons($this->right_button)
            ->setData($data_list)//设置数据
            ->fetch();//显示
    }

    /**
     * 新增
     * @return mixed
     * @author 似水星辰 [2630481389@qq.com]
     */
    public function add()
    {
        // 保存数据
        if ($this->request->isPost()) {
            // 表单数据
            $data = $this->request->post();

            // 验证
            $result = $this->validate($data, 'Apigroup');
            if (true !== $result) $this->error($result);

            if ($page = ApigroupModel::create($data)) {
                $this->success('新增成功', cookie('__forward__'));
            } else {
                $this->error('新增失败');
            }
        }

        $group = ApigroupModel::where('pid',0)->column('aid,name');
        $group[0] = '顶级分组';
        $fields = [
            ['type' => 'select', 'name' => 'module', 'title' => '所属模块', 'extra' => \app\admin\model\Module::column('name,title')],
            //['type' => 'select', 'name' => 'pid', 'title' => '所属分组', 'extra' => $group],
            ['type' => 'text', 'name' => 'name', 'title' => '分组名称']
        ];
        $this->assign('page_title', '新增接口分组');
        $this->assign('form_items', $fields);
        return $this->fetch('admin@public/add');
    }

    /**
     * 编辑
     * @param null $id 接口分组id
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
            $data['update_time'] = time();

            // 验证
            $result = $this->validate($data, 'Apigroup');
            if (true !== $result) $this->error($result);

            if (ApigroupModel::update($data)) {
                $this->success('编辑成功', cookie('__forward__'));
            } else {
                $this->error('编辑失败');
            }
        }

        $info = ApigroupModel::get(['aid' => $id]);
        $group = ApigroupModel::where('pid',0)->column('aid,name');
        $group[0] = '顶级分组';
        $fields = [
            ['type' => 'hidden', 'name' => 'aid'],
            ['type' => 'select', 'name' => 'module', 'title' => '所属模块', 'extra' => \app\admin\model\Module::column('name,title')],
            //['type' => 'select', 'name' => 'pid', 'title' => '所属分组', 'extra' => $group],
            ['type' => 'text', 'name' => 'name', 'title' => '分组名称']
        ];
        $this->assign('page_title', '编辑接口分组');
        $this->assign('form_items', $this->setData($fields, $info));
        return $this->fetch('admin@public/edit');
    }
}