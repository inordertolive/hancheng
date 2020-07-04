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

namespace app\operation\admin;

use app\admin\admin\Base;
use app\operation\model\Service as ServiceModel;
use app\operation\model\Servicegroup;
use service\Format;

/**
 * 客服列表控制器
 * @package app\Service\admin
 */
class Service extends Base
{
    /**
     * 客服列表列表
     * @return mixed
     * @author 似水星辰 [2630481389@qq.com]
     */
    public function index()
    {
        cookie('__forward__', $_SERVER['REQUEST_URI']);
        // 查询
        $map = $this->getMap();
        // 排序
        $order = $this->getOrder();
        $group = Servicegroup::where('status', 1)->column('aid,name');
        // 数据列表
        $data_list = ServiceModel::where($map)->order($order)->paginate();
        $fields = [
            ['id', 'ID'],
            ['nickname', '客服昵称'],
            ['username', '客服账号'],
            ['group', '所属分组', 'status', '', $group],
            ['avatar', '客服头像','picture'],
            ['right_button', '操作', 'btn', '', '', 'text-center']
        ];
        return Format::ins()//实例化
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
            $result = $this->validate($data, 'Service.add');
            if (true !== $result) $this->error($result);

            if ($page = ServiceModel::create($data)) {
                $this->success('新增成功', cookie('__forward__'));
            } else {
                $this->error('新增失败');
            }
        }
        //分组列表
        $group = Servicegroup::where('status', 1)->column('aid,name');

        $fields = [
            ['type' => 'text', 'name' => 'nickname', 'title' => '客服昵称', 'tips' => '', 'attr' => '', 'value' => ''],
            ['type' => 'text', 'name' => 'username', 'title' => '客服账号', 'tips' => '', 'attr' => '', 'value' => ''],
            ['type' => 'password', 'name' => 'password', 'title' => '登录密码', 'tips' => '', 'attr' => '', 'value' => ''],
            ['type' => 'select', 'name' => 'group', 'title' => '所属分组', 'extra' => $group, 'tips' => '', 'attr' => '', 'value' => '0'],
            ['type' => 'image', 'name' => 'avatar', 'title' => '客服头像', 'tips' => '', 'attr' => '', 'value' => '0'],

        ];
        $this->assign('page_title', '新增客服列表');
        $this->assign('form_items', $fields);
        return $this->fetch('admin@public/add');
    }

    /**
     * 编辑
     * @param null $id 客服列表id
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

            // 如果没有填写密码，则不更新密码
            if ($data['password'] == '') {
                unset($data['password']);
            }

            // 验证
            $result = $this->validate($data, 'Service.edit');
            if (true !== $result) $this->error($result);

            if (ServiceModel::update($data)) {
                $this->success('编辑成功', cookie('__forward__'));
            } else {
                $this->error('编辑失败');
            }
        }

        //分组列表
        $group = Servicegroup::where('status', 1)->column('aid,name');

        $info = ServiceModel::get($id);
        $fields = [
            ['type' => 'hidden', 'name' => 'id'],
            ['type' => 'text', 'name' => 'nickname', 'title' => '客服昵称', 'tips' => '', 'attr' => '', 'value' => ''],
            ['type' => 'text', 'name' => 'username', 'title' => '客服账号', 'tips' => '', 'attr' => '', 'value' => ''],
            ['type' => 'password', 'name' => 'password', 'title' => '登录密码', 'tips' => '', 'attr' => '', 'value' => ''],
            ['type' => 'select', 'name' => 'group', 'title' => '所属分组', 'extra' => $group, 'tips' => '', 'attr' => ''],
            ['type' => 'image', 'name' => 'avatar', 'title' => '客服头像', 'tips' => '', 'attr' => '', 'value' => '0'],

        ];
        $this->assign('page_title', '编辑客服列表');
        $this->assign('form_items', $this->setData($fields, $info));
        return $this->fetch('admin@public/edit');
    }
}