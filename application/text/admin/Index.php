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

namespace app\text\admin;

use app\admin\admin\Base;
use service\Format;
/**
 * 测试过滤文件控制器
 * @package app\Index\admin
 */
class Index extends Base
{
    /**
     * 测试过滤文件列表
     * @author 似水星辰 [2630481389@qq.com]
     * @return mixed
     */
    public function index()
    {
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
            $result = $this->validate($data, 'Index');
            if(true !== $result) $this->error($result);

            if ($page = IndexModel::create($data)) {
                $this->success('新增成功', cookie('__forward__'));
            } else {
                $this->error('新增失败');
            }
        }

	$fields =[
		
	];
	$this->assign('page_title','新增测试过滤文件');
	$this->assign('form_items',$fields);
        return $this->fetch('admin@public/add');
    }

    /**
     * 编辑
     * @param null $id 测试过滤文件id
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
            $result = $this->validate($data, 'Index');
            if(true !== $result) $this->error($result);

            if (IndexModel::update($data)) {
                $this->success('编辑成功', cookie('__forward__'));
            } else {
                $this->error('编辑失败');
            }
        }
	
        $info = IndexModel::get(['aid'=>$id]);
	$fields =[
		['type' => 'hidden', 'name' => 'aid'],
		
	];
	$this->assign('page_title','编辑测试过滤文件');
	$this->assign('form_items', $this->setData($fields, $info));
        return $this->fetch('admin@public/edit');
    }
}