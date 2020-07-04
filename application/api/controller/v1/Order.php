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
namespace app\api\controller\v1;

use app\api\controller\Base;
use app\common\model\Order as OrderModel;
use service\ApiReturn;
use think\Db;
/**
 * 订单接口
 * Class UserLabel
 * @package app\api\controller\v1
 */
class Order extends Base
{
    /**
     * 创建订单
     * @author 晓风<215628355@qq.com>
     */
    public function add_order($data, $user) {
        $order_type = $data['order_type'];

        $types = OrderModel::$orderTypes;
        if (!isset($types[$order_type])) {
            return ApiReturn::r(0, '', '暂不支持该订单类型');
        }

        $order = false;
        Db::startTrans();
        try {
            switch ($order_type) {
                case '1' :
                    $order = OrderModel::addRechargeOrder($data, $user);
                    break;
                case '2' :
                    $order = OrderModel::addRechargeOrder($data, $user);
                    break;
                case '3' :
                    $order = OrderModel::addGoodsOrder($data, $user);
                    break;
                default:
                    throw new \Exception("暂不支持该类型订单下单");
                    break;
            }
            Db::commit();
        } catch (\Exception $e) {
            Db::rollback();
            return ApiReturn::r(0, '', $e->getMessage());
        }

        if ($order) {
            return ApiReturn::r(1, $order, '下单成功，请向预支付接口获取支付信息');
        }
        return ApiReturn::r(0, '', '下单失败');
    }

    /**
     * 查询订单详情
     * @param $data
     * @param $user
     * @author 似水星辰 [ 2630481389@qq.com ]
     * @created 2019/9/23 19:14
     */
    public function get_order_detail($data, $user){
        $order_type = $data['order_type'];

        $types = OrderModel::$orderTypes;
        if (!isset($types[$order_type])) {
            return ApiReturn::r(0, '', '暂不支持该订单类型');
        }

        switch ($order_type) {
            case '1' :
                // 如果是充值类型，则只查订单主表即可
                $order = OrderModel::where(['order_sn'=>$data['order_sn'],'user_id'=>$user['id']])->find();
                break;
            case '2' :
                // 如果是充值类型，则只查订单主表即可
                $order = OrderModel::where(['order_sn'=>$data['order_sn'],'user_id'=>$user['id']])->find();
                break;
            case '3' :
                // 商城订单
                $order = OrderModel::where(['order_sn'=>$data['order_sn'],'user_id'=>$user['id']])
                    ->field('aid', true)
                    ->find();
                if($data['is_son']){
                    $order['order_info'] = Db::name('order_goods_info')->get(['order_sn'=>$data['order_sn']]);
                    $goods_list = Db::name('order_goods_list')->where(['order_sn'=>$data['order_sn']])->select();
                    foreach ($goods_list as &$item) {
                        $item['goods_thumb'] = get_file_url($item['goods_thumb']);
                    }
                    unset($item);
                    $order['order_goods_list'] = $goods_list;
                }
                break;
            default:

                break;
        }

        if ($order) {
            return ApiReturn::r(1, $order,'请求成功');
        }
        return ApiReturn::r(0, [],'无效订单号');
    }

    /**
     * 获取订单列表
     * @param $data
     * @param $user
     */
    public function get_list($data, $user)
    {
        $type = $data['type'] ?? 'all';
        $map = [];
        switch ($type) {
            case 'unpay':
                $map['o.status'] = 0;
                break;
            case 'unreceive':
                $map['o.status'] = [1,2];
                break;
            case 'finish':
                $map['o.status'] = [3,4];
                break;
            case 'refund':
                $map['o.status'] = [5,6];
                break;
            case 'cancel':
                $map['o.status'] = -1;
                break;
            default:
                $map[] = ['o.status', '>', -2];
                break;
        }
        $list = db('order')->alias('o')
            ->field('o.order_sn,o.status,o.payable_money,o.real_money')
            ->where('o.order_type', 3)
            ->where('o.user_id', $user['id'])
            ->where($map)
            ->order('o.aid desc')
            ->paginate(5);
        $orders = $list->items();
        foreach ($orders as &$item) {
            $goods = db('order_goods_list')->alias('g')
                ->field('g.order_sn,g.goods_id,g.goods_name,g.sku_id,g.sku_name,g.shop_price,g.num,g.goods_money,g.goods_thumb,g.order_status')
                ->where('g.order_sn', $item['order_sn'])
                ->select();
            foreach ($goods as &$g) {
                $g['goods_thumb'] = get_file_url($g['goods_thumb']);
            }
            unset($g);
            $item['goods'] = $goods;
        }
        unset($item);
        return ApiReturn::r(1, $orders, '订单列表');
    }

    /**
     * 提醒发货
     * @param $data
     * @param $user
     */
    public function remind_order($data, $user)
    {
        // order_status = 1
        $order_sn = $data['order_sn'];
        // 判断订单是否可提醒
        $order = db('order')->where('order_sn', $order_sn)->where('status', 1)->find();
        if (!$order) {
            return ApiReturn::r(0, '', '已发货,请刷新订单');
        }
        // 判断是否已提醒
        $remind = db('order_remind')->where('order_sn', $order_sn)->where('user_id', $user['id'])->find();
        if ($remind) {
            return ApiReturn::r(0, '', '已提醒');
        }

        $idata = [
            'order_sn' => $data['order_sn'],
            'user_id' => $user['id'],
            'create_time' => time()
        ];
        $rs = db('order_remind')->insert($idata);
        if ($rs) {
            // 通知后端pc
            return ApiReturn::r(1, '', '提醒成功');
        } else {
            return ApiReturn::r(0, '', '提醒失败');
        }
    }

    /**
     * 确认发货
     * @param $data
     * @param $user
     */
    public function receive_order($data, $user)
    {
        // order_status = 2 => 3
        $order_sn = $data['order_sn'];
        Db::startTrans();
        try {
            $order = Db::name('order')->where('order_sn', $order_sn)->where('status', 2)->find();
            if (!$order) {
                exception('订单不可操作，请刷新');
            }
            Db::name('order')->where('aid', $order['aid'])->update(['status'=>3]);
            Db::name('order_goods_express')->where('order_sn', $order_sn)->update(['receive_time'=>time()]);
            Db::name('order_goods_list')->where('order_sn', $order_sn)->update(['order_status'=>3]);
            Db::commit();
        } catch (\Exception $e) {
            Db::rollback();
            return ApiReturn::r(0, '', $e->getMessage());
        }
        return ApiReturn::r(1, '', '确认成功');
    }

    /**
     * 订单评价
     * @param $data
     * @param $user
     */
    public function comment($data, $user)
    {
        // order_status = 3 => 4
        $order_sn = $data['order_sn'];

        $res = $this->validate($data, 'goods/Comment');
        if (true !== $res) return ApiReturn::r(0, '', '参数有误');
        $comment = $data;

        Db::startTrans();
        try {
            $order = Db::name('order')->where('order_sn', $order_sn)->where('status', 3)->find();
            if (!$order) {
                exception('订单不可操作，请刷新');
            }
            Db::name('order')->where('order_sn', $order_sn)->update(['status'=>4]);

            Db::name('order_goods_list')->where('order_sn', $order_sn)->update(['order_status'=>4]);

            $goods_list = Db::name('order_goods_list')->where('order_sn', $order_sn)->column('distinct(goods_id)');
            $all_data = [];
            foreach ($goods_list as $item) {
                $comment['create_time'] = time();
                $comment['goods_id'] = $item;
                $all_data[] = $comment;
            }
            Db::name('goods_comment')->insertAll($all_data);
            Db::commit();
        } catch (\Exception $e) {
            Db::rollback();
            return ApiReturn::r(0, '', $e->getMessage());
        }
        return ApiReturn::r(1,'', '评论成功');
    }

    /**
     * 退单申请
     * @param $data
     * @param $user
     */
    public function refund_apply($data, $user)
    {
        // order_status = 4 => 5
        $order_sn = $data['order_sn'];

        $res = $this->validate($data, 'goods/Refund');
        if (true !== $res) return ApiReturn::r(0, '', '参数有误');
        $refund = $data;

        Db::startTrans();
        try {
            $order = Db::name('order')->where('order_sn', $order_sn)->where('status', 4)->find();
            if (!$order) {
                exception('订单不可操作，请刷新');
            }
            Db::name('order')->where('order_sn', $order_sn)->update(['status'=>5]);
            Db::name('order_goods_list')->where('order_sn',$order_sn)->update(['status'=>5]);
            $refund['user_id'] = $order['user_id'];
            $refund['create_time'] = time();
            Db::name('order_refund')->insert($refund);
            Db::commit();
        } catch (\Exception $e) {
            Db::rollback();
            return ApiReturn::r(0,''. $e->getMessage());
        }

        return ApiReturn::r(1, '', '申请成功');
    }

    /**
     * 取消订单
     * @param $data
     * @param $user
     */
    public function cancel_order($data, $user)
    {
        // order_status = 0 => -1
        $order_sn = $data['order_sn'];
        // 软删除
        Db::startTrans();
        try {
            $order = Db::name('order')->where('order_sn', $order_sn)->where('status', 0)->lock(true)->find();
            if (!$order) {
                exception('订单不可操作，请刷新');
            }
            Db::name('order')->where('aid', $order['aid'])->setField('status', -1);
            Db::name('order_goods_list')->where('order_sn', $order_sn)->setField('order_status', -1);
            Db::commit();
        } catch (\Exception $e) {
            Db::rollback();
            return ApiReturn::r(0, '', $order_sn.'whart'.$e->getMessage());
        }
        return ApiReturn::r(1, '', '删除成功');
    }
    /**
     * 删除订单
     * @param $data
     * @param $user
     */
    public function remove_order($data, $user)
    {
        // order_status = 3,4,6 => -2
        $order_sn = $data['order_sn'];

        // 软删除
        Db::startTrans();
        try {
            $order = Db::name('order')->where('order_sn', $order_sn)->where(['status'=>[0,3,4,6]])->lock(true)->find();
            if (!$order) {
                exception('订单不可操作，请刷新');
            }
            Db::name('order')->where('aid', $order['aid'])->setField('status', -2);
            Db::name('order_goods_list')->where('order_sn', $order_sn)->setField('order_status', -2);
            Db::commit();
        } catch (\Exception $e) {
            Db::rollback();
            return ApiReturn::r(0, '', $order_sn.'whart'.$e->getMessage());
        }
        return ApiReturn::r(1, '', '删除成功');
    }

    /**
     * 查看物流
     */
    public function express($data, $user)
    {
        $order_sn = $data['order_sn'];

        $express = db('order_goods_express')->where('order_sn', $order_sn)->find();

        if ($express) {
            return ApiReturn::r(1, $express, '物流信息');
        } else {
            return ApiReturn::r(0, '', '没有物流数据');
        }
    }
}
