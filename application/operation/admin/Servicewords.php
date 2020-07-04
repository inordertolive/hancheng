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
use app\operation\model\Servicewords as ServicewordsModel;
use service\Format;
/**
 * 客服常用词控制器
 * @package app\Servicewords\admin
 */
class Servicewords extends Base
{
    /**
     * 客服常用词列表
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
        $data_list = ServicewordsModel::where($map)->order($order)->paginate();
        $fields =[
		['id','ID'],['body','内容'],
		['right_button', '操作', 'btn','','','text-center']
        ];
        return Format::ins() //实例化
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
            $result = $this->validate($data, 'Servicewords');
            if(true !== $result) $this->error($result);

            if ($page = ServicewordsModel::create($data)) {
                $this->success('新增成功', cookie('__forward__'));
            } else {
                $this->error('新增失败');
            }
        }

	$fields =[
		['type'=>'text','name'=>'body','title'=>'内容','tips'=>'','attr'=>'','value'=>''],
		
	];
	$this->assign('page_title','新增客服常用词');
	$this->assign('form_items',$fields);
        return $this->fetch('admin@public/add');
    }

    /**
     * 编辑
     * @param null $id 客服常用词id
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
            $result = $this->validate($data, 'Servicewords');
            if(true !== $result) $this->error($result);

            if (ServicewordsModel::update($data)) {
                $this->success('编辑成功', cookie('__forward__'));
            } else {
                $this->error('编辑失败');
            }
        }
	
        $info = ServicewordsModel::get($id);
	$fields =[
		['type' => 'hidden', 'name' => 'id'],
		['type'=>'text','name'=>'body','title'=>'内容','tips'=>'','attr'=>'','value'=>''],
		
	];
	$this->assign('page_title','编辑客服常用词');
	$this->assign('form_items', $this->setData($fields, $info));
        return $this->fetch('admin@public/edit');
    }
}