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
use app\user\model\Address as AddressModel;
use service\Format;

/**
 * 会员地址控制器
 * Class Address
 * @package app\user\admin
 * @author 似水星辰 [ 2630481389@qq.com ]
 * @since 2019/4/3 9:30
 */
class Address extends Base
{
    /**
     * 会员地址列表
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
        $order = $this->getOrder();
        // 数据列表
        $data_list = AddressModel::alias('ad')->join('user u','ad.user_id=u.id','left')
            ->field('ad.address_id,ad.name,ad.mobile,ad.address,ad.is_default,u.user_nickname')
            ->where($map)
            ->order($order)
            ->paginate();
        $fields = [
            ['address_id', 'ID'],
            ['name', '姓名'],
            ['mobile', '收货电话'],
            ['address', '收货人地址'],
            ['is_default', '是否默认','status','',['否','是']],
            ['user_nickname', '会员昵称'],
            ['right_button', '操作', 'btn', '', '', 'text-center']
        ];
        return Format::ins()//实例化
        ->addColumns($fields)//设置字段
        ->setRightButton(['title'=>'查看详情', 'href'=>['detail', ['id'=>'__address_id__', 'layer' => 1]], 'icon'=>'fa fa-eye pr5', 'data-toggle'=>'dialog', 'class'=>'btn btn-xs mr5 btn-default btn-flat'])
        ->setData($data_list)//设置数据
        ->fetch();//显示
    }

    /**
     * 查看详细地址
     * @author 似水星辰 [ 2630481389@qq.com ]
     * @since 9:49
     * @return void
     */
    public function detail($id = 0){
        if ($id == 0){
            $this->error('参数错误');
        }

        $info = AddressModel::alias('ad')->join('user u','ad.user_id=u.id','left')->where('ad.address_id',$id)->field('ad.*,u.user_nickname')->find();
        $this->assign('info',$info);
        $fields = [
            ['type' => 'static', 'name' => 'name', 'title' => '收货人姓名'],
            ['type' => 'static', 'name' => 'mobile', 'title' => '收货电话'],
            ['type' => 'static', 'name' => 'address', 'title' => '收货人地址'],
            ['type' => 'radio', 'name' => 'is_default', 'title' => '是否默认', 'extra' => ['否','是'], 'attr'=>'disabled'],
            ['type' => 'static', 'name' => 'user_nickname', 'title' => '所属会员昵称'],

        ];
        $this->assign('page_title', '会员地址详情');
        $this->assign('form_items', $this->setData($fields, $info));
        $this->assign('btn_hide',1);
        return $this->fetch('../../admin/view/public/edit');
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
            $result = $this->validate($data, 'Address');
            if (true !== $result) $this->error($result);

            if ($page = AddressModel::create($data)) {
                $this->success('新增成功', cookie('__forward__'));
            } else {
                $this->error('新增失败');
            }
        }

        $fields = [
            ['type' => 'text', 'name' => 'user_name', 'title' => '姓名', 'tips' => '', 'attr' => '', 'value' => ''],
            ['type' => 'text', 'name' => 'user_phone', 'title' => '收货电话', 'tips' => '', 'attr' => '', 'value' => ''],
            ['type' => 'text', 'name' => 'user_address', 'title' => '收货人地址', 'tips' => '', 'attr' => '', 'value' => ''],
            ['type' => 'radio', 'name' => 'is_default', 'title' => '是否默认', 'tips' => '', 'attr' => '', 'value' => '1'],
            ['type' => 'text', 'name' => 'user_id', 'title' => '会员昵称', 'tips' => '', 'attr' => '', 'value' => '0'],

        ];
        $this->assign('page_title', '新增会员地址');
        $this->assign('form_items', $fields);
        return $this->fetch('../../admin/view/public/add');
    }

    /**
     * 编辑
     * @param null $id 会员地址id
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
            $result = $this->validate($data, 'Address');
            if (true !== $result) $this->error($result);

            if (AddressModel::update($data)) {
                $this->success('编辑成功', cookie('__forward__'));
            } else {
                $this->error('编辑失败');
            }
        }

        $info = AddressModel::get($id);
        $fields = [
            ['type' => 'hidden', 'name' => 'address_id'],
            ['type' => 'text', 'name' => 'user_name', 'title' => '姓名', 'tips' => '', 'attr' => '', 'value' => ''],
            ['type' => 'text', 'name' => 'user_phone', 'title' => '收货电话', 'tips' => '', 'attr' => '', 'value' => ''],
            ['type' => 'text', 'name' => 'user_address', 'title' => '收货人地址', 'tips' => '', 'attr' => '', 'value' => ''],
            ['type' => 'radio', 'name' => 'is_default', 'title' => '是否默认', 'tips' => '', 'attr' => '', 'value' => '1'],
            ['type' => 'text', 'name' => 'user_id', 'title' => '会员昵称', 'tips' => '', 'attr' => '', 'value' => '0'],

        ];
        $this->assign('page_title', '编辑会员地址');
        $this->assign('form_items', $this->setData($fields, $info));
        return $this->fetch('../../admin/view/public/edit');
    }
}