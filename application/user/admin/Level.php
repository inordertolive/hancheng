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
use app\user\model\Level as LevelModel;
use service\Format;

/**
 * 会员等级控制器
 * @package app\Level\admin
 */
class Level extends Base
{
    /**
     * 会员等级列表
     * @author 似水星辰 [2630481389@qq.com]
     * @return mixed
     */
    public function index()
    {
        cookie('__forward__', $_SERVER['REQUEST_URI']);
        // 查询
        $map = $this->getMap();
        // 排序
        $order = $this->getOrder();
        // 数据列表
        $data_list = LevelModel::where($map)->order($order)->paginate();
        $fields = [
            ['id', 'ID'],
            ['name', '等级名称'],
            ['upgrade_score', '升级所需分数'],
            ['levelid', '等级标识'],
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
     * @author 似水星辰 [2630481389@qq.com]
     * @return mixed
     */
    public function add()
    {
        // 保存数据
        if ($this->request->isPost()) {
            // 表单数据
            $data = $this->request->post();

            // 验证
            $result = $this->validate($data, 'Level');
            if (true !== $result) $this->error($result);

            if ($page = LevelModel::create($data)) {
                $this->success('新增成功', cookie('__forward__'));
            } else {
                $this->error('新增失败');
            }
        }

        $fields = [
            ['type' => 'text', 'name' => 'name', 'title' => '等级名称', 'tips' => '', 'attr' => '', 'value' => ''],
            ['type' => 'number', 'name' => 'upgrade_score', 'title' => '升级所需分数', 'tips' => '', 'attr' => '', 'value' => '0'],
            ['type' => 'number', 'name' => 'levelid', 'title' => '等级标识', 'tips' => '', 'attr' => '', 'value' => '0'],

        ];
        $this->assign('page_title', '新增会员等级');
        $this->assign('form_items', $fields);
        return $this->fetch('../../admin/view/public/add');
    }

    /**
     * 编辑
     * @param null $id 会员等级id
     * @author 似水星辰 [2630481389@qq.com]
     * @return mixed
     */
    public function edit($id = null)
    {
        if ($id === null) $this->error('缺少参数');

        // 保存数据
        if ($this->request->isPost()) {
            // 表单数据
            $data = $this->request->post();

            // 验证
            $result = $this->validate($data, 'Level');
            if (true !== $result) $this->error($result);

            if (LevelModel::update($data)) {
                $this->success('编辑成功', cookie('__forward__'));
            } else {
                $this->error('编辑失败');
            }
        }

        $info = LevelModel::get($id);
        $fields = [
            ['type' => 'hidden', 'name' => 'id'],
            ['type' => 'text', 'name' => 'name', 'title' => '等级名称', 'tips' => '', 'attr' => '', 'value' => ''],
            ['type' => 'number', 'name' => 'upgrade_score', 'title' => '升级所需分数', 'tips' => '', 'attr' => '', 'value' => '0'],
            ['type' => 'number', 'name' => 'levelid', 'title' => '等级标识', 'tips' => '', 'attr' => '', 'value' => '0'],

        ];
        $this->assign('page_title', '编辑会员等级');
        $this->assign('form_items', $this->setData($fields, $info));
        return $this->fetch('../../admin/view/public/edit');
    }
}