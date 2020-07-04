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
use app\user\model\Label as LabelModel;
use service\Format;
/**
 * 会员标签控制器
 * @package app\Label\admin
 */
class Label extends Base
{
    /**
     * 会员标签列表
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
        $data_list = LabelModel::where($map)->order($order)->paginate();
        $fields =[
            ['id','ID'],
            ['type_name','分类名'],
            ['value','属性值'],
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
            $result = $this->validate($data, 'Label');
            if(true !== $result) $this->error($result);

            if ($page = LabelModel::create($data)) {
                $this->success('新增成功', cookie('__forward__'));
            } else {
                $this->error('新增失败');
            }
        }

	$fields =[
		['type'=>'text','name'=>'type_name','title'=>'分类名','tips'=>'','attr'=>'','value'=>''],
		['type'=>'text','name'=>'value','title'=>'属性值','tips'=>'请用英文逗号“,”分隔','attr'=>'','value'=>''],
		
	];
	$this->assign('page_title','新增会员标签');
	$this->assign('form_items',$fields);
        return $this->fetch('../../admin/view/public/add');
    }

    /**
     * 编辑
     * @param null $id 会员标签id
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
            $result = $this->validate($data, 'Label');
            if(true !== $result) $this->error($result);

            if (LabelModel::update($data)) {
                $this->success('编辑成功', cookie('__forward__'));
            } else {
                $this->error('编辑失败');
            }
        }
	
        $info = LabelModel::get($id);
	$fields =[
		['type' => 'hidden', 'name' => 'id'],
		['type'=>'text','name'=>'type_name','title'=>'分类名','tips'=>'','attr'=>'','value'=>''],
		['type'=>'text','name'=>'value','title'=>'属性值','tips'=>'请用英文逗号“,”分隔','attr'=>'','value'=>''],
		
	];
	$this->assign('page_title','编辑会员标签');
	$this->assign('form_items', $this->setData($fields, $info));
        return $this->fetch('../../admin/view/public/edit');
    }
}