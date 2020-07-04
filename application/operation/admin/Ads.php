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
use app\operation\model\Ads as AdsModel;
use app\operation\model\AdsType;
use service\Format;

/**
 * 广告控制器
 * @package app\operation\admin
 */
class Ads extends Base
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
        $data_list = AdsModel::where($map)->order($order)->paginate();

        $list_type = AdsType::where('status', 1)->column('id,name');
		$fields =[
			['id', 'ID'],
            ['name', '广告名称', 'text'],
            ['typeid', '所属广告位', 'status','',$list_type],
			['thumb', '图片', 'picture'],
            ['create_time', '创建时间', '','','','text-center'],
            ['update_time', '更新时间', '','','','text-center'],
            ['status', '状态', 'status','', '','text-center'],
			['right_button', '操作', 'btn','','','text-center']
		];

		return Format::ins() //实例化
			->addColumns($fields)//设置字段
			->setTopButtons($this->top_button)
			->setTopButton(['title'=>'广告位管理','href'=>['operation/AdsType/index'],'icon'=>'fa fa-plus pr5','class'=>'btn btn-sm mr5 btn-success btn-flat'])
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

            if ($advert = AdsModel::create($data)) {
                $this->success('新增成功', cookie('__forward__'));
            } else {
                $this->error('新增失败');
            }
        }

        $list_type = AdsType::where('status', 1)->column('id,name');
        array_unshift($list_type, '默认广告位');
		$fields =[
			['type' => 'select', 'name' => 'typeid', 'title' => '所属广告位', 'tips' => '', 'extra' => $list_type, 'value' => 0],
            ['type' => 'text', 'name' => 'name', 'title' => '广告名称'],
            ['type' => 'image', 'name' => 'thumb', 'title' => '图片', 'tips' => ''],
            ['type' => 'text', 'name' => 'content', 'title' => '文字内容', 'tips' => ''],
            ['type' => 'text', 'name' => 'href', 'title' => '链接', 'tips' => ''],
            ['type' => 'text', 'name' => 'width', 'title' => '宽度', 'tips' => '不用填写单位，只需填写具体数字'],
            ['type' => 'text', 'name' => 'height', 'title' => '高度', 'tips' => '不用填写单位，只需填写具体数字'],
            ['type' => 'radio', 'name' => 'status', 'title' => '立即启用', 'tips' => '', 'extra' => ['否', '是'], 'value' => 1]
		];

		$this->assign('page_title','新增广告');
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

            if (AdsModel::update($data)) {
                // 记录行为
                action_log('ads_edit', 'operation_ads', $id, UID, $data['name']);
                $this->success('编辑成功', 'index');
            } else {
                $this->error('编辑失败');
            }
        }

        $list_type = AdsType::where('status', 1)->column('id,name');

        $info = AdsModel::get($id);
		$fields =[
			 ['type' => 'hidden', 'name' => 'id'],
             ['type' => 'text', 'name' => 'name', 'title' => '广告名称'],
             ['type' => 'select', 'name' => 'typeid', 'title' => '所属广告位', 'extra' => $list_type],
			 ['type' => 'image', 'name' => 'thumb', 'title' => '图片', 'tips' => ''],
             ['type' => 'text', 'name' => 'content', 'title' => '文字内容', 'tips' => ''],
             ['type' => 'text', 'name' => 'href', 'title' => '链接', 'tips' => ''],
             ['type' => 'text', 'name' => 'width', 'title' => '宽度', 'tips' => '不用填写单位，只需填写具体数字'],
             ['type' => 'text', 'name' => 'height', 'title' => '高度', 'tips' => '不用填写单位，只需填写具体数字'],
             ['type' => 'radio', 'name' => 'status', 'title' => '立即启用', 'extra' => ['否', '是']],
		];
		$this->assign('page_title','编辑广告位');
		$this->assign('form_items', $this->setData($fields, $info));
        return $this->fetch('admin@public/edit');
        
    }
}