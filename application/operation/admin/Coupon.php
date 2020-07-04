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
use app\operation\model\Coupon as CouponModel;
use app\operation\model\CouponRecord;
use app\user\model\User as UserModel;
use service\Format;
use think\Request;

/**
 * 优惠券控制器
 * Class Coupon
 * @package app\admin\controller
 */
class Coupon extends Base
{

    /**
     * 优惠券列表
     * @return \think\response\View
     * @throws \think\exception\DbException
     * @author 似水星辰 [ 2630481389@qq.com ]
     */

    public function index()
    {
        cookie('__forward__', $_SERVER['REQUEST_URI']);
        // 查询
        $map = $this->getMap();
        // 排序
        $order = $this->getOrder('id desc');
        // 数据列表
        $data_list = CouponModel::where($map)->order($order)->paginate();

        $fields = [
            ['id', 'ID'],
            ['name', '优惠券名称'],
            ['start_time', '开始领取时间', 'callback', function ($data) {
                return date('Y-m-d', $data) . ' 00:00:00';
            }],
            ['end_time', '领取结束时间', 'callback', function ($v) {
                return date('Y-m-d', $v) . ' 23:59:59';
            }],
            ['money', '面额'],
            ['min_order_money', '最低使用金额'],
            ['valid_day', '有效天数'],
            ['stock', '总张数'],
			['last_stock', '剩余张数'],
            ['method', '领取方式', 'status', '', ['系统发放', '首页弹窗', '手动领取']],
            ['create_time', '创建时间', '', '', '', 'text-center'],
            ['status', '状态', 'status', '', ['未开启', '可领取', '已领完'], 'text-center'],
            ['right_button', '操作', 'btn', '', '', 'text-center']
        ];

        return Format::ins()//实例化
        ->addColumns($fields)//设置字段
        ->setTopButtons($this->top_button)
            ->setRightButton(['title' => '领取记录', 'href' => ['receiving', ['id' => '__id__']], 'icon' => 'fa fa-list pr5', 'class' => 'btn btn-xs mr5 btn-success btn-flat'])
            ->setRightButton(['title' => '手动发放', 'href' => ['send_coupon', ['id' => '__id__']], 'icon' => 'fa fa-send-o pr5', 'class' => 'btn btn-xs mr5 btn-success btn-flat'])
            ->setRightButtons($this->right_button)
            ->setData($data_list)//设置数据
            ->fetch();//显示
    }

    /**
     * 添加优惠券
     * @return void
     * @author 似水星辰 [ 2630481389@qq.com ]
     */
    public function add()
    {
        if ($this->request->isPost()) {
            // 表单数据
            $data = $this->request->post();
            $data['last_stock'] = $data['stock'];
            // 验证
            $result = $this->validate($data, 'Coupon');
            if (true !== $result) $this->error($result);

            $data['start_time'] = strtotime($data['start_time'] . " 00:00:00");
            $data['end_time'] = strtotime($data['end_time'] . " 23:59:59");

            if ($res = CouponModel::create($data)) {
                $this->success('新增成功', cookie('__forward__'));
            } else {
                $this->error('新增失败');
            }
        }

        $fields = [
            ['type' => 'text', 'name' => 'name', 'title' => '优惠券名称'],
            ['type' => 'date', 'name' => 'start_time', 'title' => '开始领取时间', 'tips' => ''],
            ['type' => 'date', 'name' => 'end_time', 'title' => '领取结束时间', 'tips' => ''],
            ['type' => 'text', 'name' => 'money', 'title' => '面额', 'tips' => ''],
            ['type' => 'number', 'name' => 'min_order_money', 'title' => '最低使用金额', 'tips' => '不用填写单位，只需填写具体数字'],
            ['type' => 'number', 'name' => 'valid_day', 'title' => '有效天数', 'tips' => '不用填写单位，只需填写具体数字'],
            ['type' => 'number', 'name' => 'stock', 'title' => '总张数', 'tips' => '不用填写单位，只需填写具体数字'],
            ['type' => 'radio', 'name' => 'method', 'title' => '领取方式', 'extra' => ['系统发放', '首页弹窗', '手动领取'], 'value' => 0],
            ['type' => 'radio', 'name' => 'status', 'title' => '状态', 'tips' => '', 'extra' => ['关闭', '可领取'], 'value' => 0],
            ['type' => 'textarea', 'name' => 'content', 'title' => '请填写优惠券内容', 'tips' => '']
        ];

        $this->assign('page_title', '新增优惠券');
        $this->assign('form_items', $fields);
        $this->assign('set_script', ['/static/plugins/layer/laydate/laydate.js']);
        return $this->fetch('admin@public/add');
    }

    /**
     * 编辑优惠券
     * @param int $id 优惠券id
     * @return \think\response\View
     * @author 似水星辰 [ 2630481389@qq.com ]
     */
    public function edit($id = 0)
    {
        if ($this->request->isPost()) {
            // 表单数据
            $data = $this->request->post();

            // 验证
            $result = $this->validate($data, 'Coupon');
            if (true !== $result) $this->error($result);

            $data['start_time'] = strtotime($data['start_time'] . " 00:00:00");
            $data['end_time'] = strtotime($data['end_time'] . " 23:59:59");

            if (CouponModel::update($data)) {
                // 记录行为
                action_log('coupon_edit', 'operation_coupon', $id, UID, $data['name']);
                $this->success('编辑成功', 'index');
            } else {
                $this->error('编辑失败');
            }
        }

        if (!$id) {
            $this->error('参数错误');
        }

        // 读取优惠券信息
        $info = CouponModel::get($id);
        $info['start_time'] = date('Y-m-d', $info['start_time']);
        $info['end_time'] = date('Y-m-d', $info['end_time']);

        $fields = [
            ['type' => 'hidden', 'name' => 'id'],
            ['type' => 'text', 'name' => 'name', 'title' => '优惠券名称'],
            ['type' => 'date', 'name' => 'start_time', 'title' => '开始领取时间', 'tips' => ''],
            ['type' => 'date', 'name' => 'end_time', 'title' => '领取结束时间', 'tips' => ''],
            ['type' => 'text', 'name' => 'money', 'title' => '面额', 'tips' => ''],
            ['type' => 'number', 'name' => 'min_order_money', 'title' => '最低使用金额', 'tips' => '不用填写单位，只需填写具体数字'],
            ['type' => 'number', 'name' => 'valid_day', 'title' => '有效天数', 'tips' => '不用填写单位，只需填写具体数字'],
            ['type' => 'number', 'name' => 'stock', 'title' => '库存张数', 'tips' => '不用填写单位，只需填写具体数字'],
            ['type' => 'radio', 'name' => 'method', 'title' => '领取方式', 'extra' => ['系统发放', '首页弹窗', '手动领取']],
            ['type' => 'radio', 'name' => 'status', 'title' => '状态', 'tips' => '', 'extra' => ['关闭', '可领取']],
            ['type' => 'textarea', 'name' => 'content', 'title' => '优惠券内容', 'tips' => '']
        ];

        $this->assign('page_title', '编辑优惠券');
        $this->assign('set_script', ['/static/plugins/layer/laydate/laydate.js']);
        $this->assign('form_items', $this->setData($fields, $info));
        return $this->fetch('admin@public/edit');

    }

    /**
     * 优惠券领取记录
     * @param int $id 优惠券id
     * @return mixed
     * @throws \think\exception\DbException
     * @author 似水星辰 [ 2630481389@qq.com ]
     */
    public function receiving($id = 0)
    {
        cookie('__forward__', $_SERVER['REQUEST_URI']);
        // 查询
        $map['cid'] = $id;
        // 排序
        $order = $this->getOrder('cr.start_time desc');
        // 数据列表
        $data_list = CouponRecord::alias('cr')->join('operation_coupon c','cr.cid=c.id')->where($map)->field('cr.*,c.name,c.money,c.min_order_money')->order($order)->paginate();

        $fields = [
            ['id', 'ID'],
            ['name', '优惠券名称'],
            ['user_id', '领取人','callback','get_nickname'],
            ['start_time', '领取时间', 'callback', function ($data) {
                return date('Y-m-d H:i:s', $data);
            }, '', 'text-center'],
            ['end_time', '过期时间', 'callback', function ($v) {
                return date('Y-m-d H:i:s', $v);
            }, '', 'text-center'],
            ['money', '面额'],
            ['min_order_money', '最低使用金额'],
            ['status', '状态', 'status', '', ['已过期', '未使用', '占用中','已使用','已失效'], 'text-center'],
            ['right_button', '操作', 'btn', '', '', 'text-center']
        ];

        return Format::ins()//实例化
        ->addColumns($fields)//设置字段
        ->setRightButton(['title' => '删除优惠券', 'href' => ['delete_coupon_record', ['id' => '__id__']], 'icon' => 'fa fa-times pr5', 'class' => 'btn btn-xs mr5 btn-danger btn-flat ajax-get confirm'])
            ->setData($data_list)//设置数据
            ->fetch();//显示
    }

    /** 删除会员的优惠券
     * @param $id
     * @return void
     * @author 似水星辰 [ 2630481389@qq.com ]
     */
    public function delete_coupon_record($id){
        if (!$id) {
            $this->error('参数错误');
        }

        $res = CouponRecord::where('id',$id)->delete();
        if($res){
            $this->success('删除成功');
        }

        $this->error('删除失败');
    }

    /**
     * 发放优惠券
     * @param $id
     * @author 风情云淡
     * @return mixed
     */
    function send_coupon($id = 0){
        $couponId = $id;
        if($this->request->isPost()){
            $data = $this->request->post();
            $couponId = $data['coupon_id'];
            $couponType = CouponModel::where(['id'=>$data['coupon_id']])->field("start_time,end_time,stock,last_stock,method")->find();
            if($couponType['method'] != 0){
                return json(['status'=>0,'msg'=>'该优惠券类型不支持发放']);
            }
            if($couponType['last_stock']<=0 && $couponType['stock'] > 0){
                //$this->error("已经发放完了");
                return json(['status'=>0,'msg'=>'已经发放完了']);
            }
            $hasCouponUserId = CouponRecord::where(['cid'=>$couponId])->column("user_id");
            $insertData = [];
            if($data['type'] == 0){
                return json(['status'=>0,'msg'=>'请选择发放类型']);
            }
            else if($data['type'] == 1){
                //随机发放
                $where = [];
                if($hasCouponUserId){
                    $where[] = ['id', 'not in', $hasCouponUserId];
                }
                $userIds = UserModel::where($where)->column("id");
                if(count($userIds) > $couponType['last_stock']){
                    $userIds =  array_rand($userIds,$couponType['last_stock']);
                    $number = count($userIds);
                }else{
                  $number =   count($userIds);
                }
            }else{
                //指定用户发放
                $userIds = $data['user_ids'];
                $number = count($userIds);
                if($number > $couponType['last_stock']){
                    //$this->error('发放数量超过总数量');
                    return json(['status'=>0,'msg'=>'发放数量超过总数量']);
                }
            }

            foreach($userIds as &$value){
                if($hasCouponUserId){
                    if(in_array($value,$hasCouponUserId)){
                        continue;
                    }
                }
                $insertData[] =[
                    'cid' => $couponId,
                    'user_id' => $value,
                    'start_time' => time(),
                    'end_time' => $couponType['end_time'],
                    'status' => 1
                ];
            }
            CouponRecord::startTrans();
            try {
                CouponRecord::insertAll($insertData);
                CouponModel::where(['id'=>$data['coupon_id']])->setDec("last_stock",count($insertData));

                // 提交事务
                CouponRecord::commit();
            } catch (\Exception $e) {
                // 回滚事务
                CouponRecord::rollback();
                return json(['status'=>0,'msg'=>'发放失败']);
            }
            return json(['status'=>1,'msg'=>'发放成功']);
        }
        //获得用户信息
        $hasCouponUserId = CouponRecord::where(['cid'=>$couponId])->column("user_id");
        $condition = [];
        if($hasCouponUserId){
            $condition[] = ['id', 'not in', $hasCouponUserId];
        }
        $userList = UserModel::where($condition)->field("id,mobile,user_nickname")->paginate(20);
        $this->assign('user_list',$userList);
        $this->assign('coupon_id',$couponId);
        return $this->fetch();
    }

}
