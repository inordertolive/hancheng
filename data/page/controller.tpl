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

namespace app\{module}\admin;

use app\admin\admin\Base;
use app\{module}\model\{model} as {model}Model;
use service\Format;
/**
 * {title}控制器
 * @package app\{model}\admin
 */
class {model} extends Base
{
    /**
     * {title}列表
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
        $data_list = {model}Model::where($map)->order($order)->paginate();
        $fields =[
		{fields}
		['right_button', '操作', 'btn','','','text-center']
        ];
        return Format::ins() //实例化
		->setPrimaryKey('aid')
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
            $result = $this->validate($data, '{model}');
            if(true !== $result) $this->error($result);

            if ($page = {model}Model::create($data)) {
                $this->success('新增成功', cookie('__forward__'));
            } else {
                $this->error('新增失败');
            }
        }

	$fields =[
		{fields_add}
	];
	$this->assign('page_title','新增{title}');
	$this->assign('form_items',$fields);
        return $this->fetch('admin@public/add');
    }

    /**
     * 编辑
     * @param null $id {title}id
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
	    $data['update_time'] = time();

            // 验证
            $result = $this->validate($data, '{model}');
            if(true !== $result) $this->error($result);

            if ({model}Model::update($data)) {
                $this->success('编辑成功', cookie('__forward__'));
            } else {
                $this->error('编辑失败');
            }
        }
	
        $info = {model}Model::get(['aid'=>$id]);
	$fields =[
		['type' => 'hidden', 'name' => 'aid'],
		{fields_add}
	];
	$this->assign('page_title','编辑{title}');
	$this->assign('form_items', $this->setData($fields, $info));
        return $this->fetch('admin@public/edit');
    }
}