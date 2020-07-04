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
use app\operation\model\NavType as NavTypeModel;
use service\Format;

/**
 * 广告分类控制器
 * @package app\operation\admin
 */
class NavType extends Base
{
    /**
     * 广告列表
     * @author 似水星辰 [2630481389@qq.com]
     * @return mixed
     */
    public function index()
    {
		 cookie('__forward__', $_SERVER['REQUEST_URI']);
        // 查询
        $map = $this->getMap();
        // 排序
        $order = $this->getOrder('update_time desc');
        // 数据列表
        $data_list = NavTypeModel::where($map)->order($order)->paginate();
		$fields =[
			['id', 'ID'],
            ['name', '导航位位名称', 'text'],
            ['create_time', '创建时间', '','','','text-center'],
            ['update_time', '更新时间', '','','','text-center'],
            ['status', '状态', 'status','', ['禁用','正常'],'text-center'],
			['right_button', '操作', 'btn','','','text-center']
		];

		return Format::ins() //实例化
			->addColumns($fields)//设置字段
			->setTopButtons($this->top_button)
			->setTopButton(['title'=>'导航管理','href'=>['operation/nav/index'],'icon'=>'fa fa-plus pr5','class'=>'btn btn-sm mr5 btn-success btn-flat'])
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
            $result = $this->validate($data, 'NavType');
            if(true !== $result) $this->error($result);

            if ($type = NavTypeModel::create($data)) {
                $this->success('新增成功', cookie('__forward__'));
            } else {
                $this->error('新增失败');
            }
        }
		$fields =[
			['type' => 'text', 'name' => 'name', 'title' => '导航位名称'],
			['type' => 'radio', 'name' => 'status', 'title' => '立即启用', 'tips' =>'', 'extra' => ['否', '是'], 'value' => 1]
		];

		$this->assign('page_title','新增导航位');
		$this->assign('form_items',$fields);
        return $this->fetch('admin@public/add');
    }

    /**
     * 编辑
     * @param null $id 广告分类id
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
            $result = $this->validate($data, 'AdsType');
            if(true !== $result) $this->error($result);

            if (NavTypeModel::update($data)) {
                $this->success('编辑成功', cookie('__forward__'));
            } else {
                $this->error('编辑失败');
            }
        }
		
        $info = NavTypeModel::get($id);
		$fields =[
			['type' => 'hidden', 'name' => 'id'],
			['type' => 'text', 'name' => 'name', 'title' => '导航位名称'],
			['type' => 'radio', 'name' => 'status', 'title' => '立即启用', 'tips' =>'', 'extra' => ['否', '是'], 'value' => 1]
		];
		$this->assign('page_title','编辑导航位');
		$this->assign('form_items', $this->setData($fields, $info));
        return $this->fetch('admin@public/edit');
    }
}