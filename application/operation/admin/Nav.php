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
use app\operation\model\Nav as NavModel;
use app\operation\model\NavType;
use service\Format;

/**
 * 导航控制器
 * @package app\operation\admin
 */
class Nav extends Base
{
    /**
     * 导航列表
     * @author 似水星辰 [2630481389@qq.com]
     * @return mixed
     * @throws \think\exception\DbException
     */
    public function index()
    {
		cookie('__forward__', $_SERVER['REQUEST_URI']);
        // 查询
        $map = $this->getMap();
        // 排序
        $order = $this->getOrder('update_time desc');
        // 数据列表
        $data_list = NavModel::where($map)->order($order)->paginate();

        $list_type = NavType::where('status', 1)->column('id,name');
        $list_type = $list_type + ['默认类别'];

		$fields =[
			['id', 'ID'],
            ['name', '导航名称', 'text'],
            ['typeid', '所属导航位', 'status','',$list_type],
			['thumb', '图片', 'picture'],
            ['create_time', '创建时间', '','','','text-center'],
            ['update_time', '更新时间', '','','','text-center'],
            ['status', '状态', 'status','', ['禁用','正常'],'text-center'],
			['right_button', '操作', 'btn','','','text-center']
		];

		return Format::ins() //实例化
			->addColumns($fields)//设置字段
			->setTopButtons($this->top_button)
			->setTopButton(['title'=>'导航位管理','href'=>['operation/NavType/index'],'icon'=>'fa fa-plus pr5','class'=>'btn btn-sm mr5 btn-success btn-flat'])
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
            $result = $this->validate($data, 'Ads.add');
            if (true !== $result) $this->error($result);

            if ($advert = NavModel::create($data)) {
                $this->success('新增成功', cookie('__forward__'));
            } else {
                $this->error('新增失败');
            }
        }

        $list_type = NavType::where('status', 1)->column('id,name');
		$fields =[
			['type' => 'select', 'name' => 'typeid', 'title' => '所属导航位', 'tips' => '', 'extra' => $list_type, 'value' => 0],
            ['type' => 'text', 'name' => 'name', 'title' => '导航名称'],
            ['type' => 'image', 'name' => 'thumb', 'title' => '图片', 'tips' => ''],
            ['type' => 'text', 'name' => 'href', 'title' => '链接', 'tips' => ''],
            ['type' => 'text', 'name' => 'sort', 'title' => '排序', 'tips' => ''],
            ['type' => 'radio', 'name' => 'status', 'title' => '立即启用', 'tips' => '', 'extra' => ['否', '是'], 'value' => 1]
		];

		$this->assign('page_title','新增导航');
		$this->assign('form_items',$fields);
        return $this->fetch('admin@public/add');
    }

    /**
     * 编辑
     * @param null $id 广告id
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
            $result = $this->validate($data, 'Ads.edit');
            if (true !== $result) $this->error($result);

            if (NavModel::update($data)) {
                // 记录行为
                action_log('nav_edit', 'operation_nav', $id, UID, $data['name']);
                $this->success('编辑成功', 'index');
            } else {
                $this->error('编辑失败');
            }
        }

        $list_type = NavType::where('status', 1)->column('id,name');

        $info = NavModel::get($id);
        $list_type = NavType::where('status', 1)->column('id,name');
		$fields =[
            ['type' => 'hidden', 'name' => 'id'],
            ['type' => 'select', 'name' => 'typeid', 'title' => '所属导航位', 'extra' => $list_type],
            ['type' => 'text', 'name' => 'name', 'title' => '导航名称'],
            ['type' => 'image', 'name' => 'thumb', 'title' => '图片', 'tips' => ''],
            ['type' => 'text', 'name' => 'href', 'title' => '链接', 'tips' => ''],
            ['type' => 'text', 'name' => 'sort', 'title' => '排序', 'tips' => ''],
            ['type' => 'radio', 'name' => 'status', 'title' => '立即启用', 'extra' => ['否', '是']],
		];
		$this->assign('page_title','编辑广告位');
		$this->assign('form_items', $this->setData($fields, $info));
        return $this->fetch('admin@public/edit');
        
    }
}