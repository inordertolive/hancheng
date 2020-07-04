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

namespace app\operation\model;

use think\Model as ThinkModel;

/**
 * 优惠券领取模型
 * @package app\operation\model
 */
class CouponRecord extends ThinkModel
{
    // 设置当前模型对应的完整数据表名称
    protected $table = '__OPERATION_COUPON_RECORD__';

    // 自动写入时间戳
    protected $autoWriteTimestamp = true;
    /**
     *
     * @return \think\model\relation\HasOne
     */
    public function operation_coupon()
    {
        return $this->hasOne('OperationCoupon','c_id');
    }

    /**
     * 优惠券列表
     * @param $userId
     * @param $type
     * @author  风轻云淡
     * @return array|false|\PDOStatement|string|\think\Collection
     */
    public function get_coupon_list($userId, $type, $orderPrice){
        $where[] = ['user_id', 'eq', $userId];
        $nowTime = time();
        $sort = "cr.end_time desc";
        switch ($type){
            case 1: //全部
                break;
            case 2: //待使用
                $where[] = ['cr.status', 'eq', 1];
                $where[] = ['cr.start_time', 'lt', $nowTime];
                $where[] = ['cr.end_time', 'gt', $nowTime];
                break;
            case 3: //已过期
                $where[] = ['cr.status', 'eq', 4];
                $where[] = ['cr.end_time', 'lt', $nowTime];
                break;
        }
        if($orderPrice > 0){
            $sort = "oc.money desc";
            $where[] = ['oc.min_order_money', 'elt', $orderPrice];
        }
        $couponList = CouponRecord::alias("cr")->join("__OPERATION_COUPON__ oc","oc.id=cr.cid")
                        ->field("cr.id,cr.end_time,cr.status,oc.money,oc.min_order_money,oc.name as coupon_name")
                        ->where($where)
                        ->order($sort)
                        ->select();
        return $couponList;

    }

    /**
     * 优惠券详情
     * @param $where
     * @author  风轻云淡
     * @return array|false|null|\PDOStatement|string|ThinkModel
     */
    function get_user_coupon($where){
        $coupon_detail = CouponRecord::alias("cr")->join("__OPERATION_COUPON__ oc","oc.id=cr.cid")
            ->where($where)
            ->field("cr.id,cr.end_time,cr.status,cr.end_time,oc.money,oc.min_order_money,oc.name as coupon_name,oc.content")->find();
        if($coupon_detail){
            $coupon_detail['content'] = $coupon_detail['content'] ? $coupon_detail['content'] : "";
            $coupon_detail['end_time'] = date("Y-m-d H:i",$coupon_detail['end_time']);
        }
        return $coupon_detail;
    }

    /**
     * 修改优惠券是否过期
     * @param $userId
     * @author  风轻云淡
     * @return int|string
     */
    function edit_coupon($userId){
        $where[] = ['user_id', 'eq', $userId];
        $where[] = ['status', 'eq', 1];
        $where[] = ['end_time', 'lt', time()];
        $res = CouponRecord::where($where)->update(['status'=>4]);
        return $res;
    }

    /**
     * 获得可用优惠券
     * @param $userId 用户id
     * @param $orderPrice 订单金额
     * @author 风轻云淡
     * @return array|false|null|\PDOStatement|string|ThinkModel
     */
    public static function get_best_coupon($userId, $orderPrice){
        $nowTime = time();
        $where[] = ['cr.user_id', 'eq', $userId];
        $where[] = ['cr.status', 'eq', 1];
        $where[] = ['min_order_money', 'elt', $orderPrice];
        $where[] = ['cr.start_time', 'elt', $nowTime];
        $where[] = ['cr.end_time', 'egt', $nowTime];
        $couponInfo = CouponRecord::alias("cr")->join("__OPERATION_COUPON__ oc","oc.id=cr.cid")
                    ->where($where)
                    ->field("cr.id,oc.name,oc.money,oc.min_order_money,cr.end_time")
                    ->order("money desc")
                    ->select();
        return $couponInfo ? $couponInfo :[];
    }
}