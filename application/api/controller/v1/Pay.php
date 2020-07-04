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
use app\common\model\Order;
use service\ApiReturn;
use think\Db;
/**
 * 支付签名接口
 * Class UserLabel
 * @package app\api\controller\v1
 */
class Pay extends Base
{
    /**
     * 获取微信开放平台预支付订单
     * @param $data
     * @return mixed
     * @author 似水星辰 [ 2630481389@qq.com ]
     */
    public function get_wxpay($data)
    {
        //处理平台自己的订单
        $order = Order::where("order_sn", $data['order_sn'])->find();
        if (!$order) {
            return ApiReturn::r(0, [], '订单不存在');
        }
        if ($order['pay_status'] > 0) {
            return ApiReturn::r(0, [], '该订单支付状态已发生改变');
        }

        if ($order['pay_type'] =='alipay') {
            return ApiReturn::r(0, [], '请使用发起支付宝支付接口');
        }

        if ($order['pay_type'] =='appleiap') {
            return ApiReturn::r(0, [], '请使用发起苹果支付接口');
        }

        $data = array(
            'body' => Order::$orderTypes[$order['order_type']],
            'total_fee' => $order['payable_money'],
            'out_trade_no' => $order['order_sn'],
            'notify_url'=> config('web_site_domain').'/index/pay/wxapp_notify'
        );
        try {
            $arr = addons_action('WeChat', 'AppPay', 'pay', [$data]);
        } catch (\Exception $e) {
            return ApiReturn::r(0, [], '调取支付失败' . $e->getMessage());
        }

        if ($arr) {
            return ApiReturn::r(1, $arr, '请按照此配置调起支付');
        }
        return ApiReturn::r(0, [], '调取支付失败');
    }

    /**
     * 获取支付宝预支付订单
     * @param $data
     * @return mixed
     * @author 似水星辰 [ 2630481389@qq.com ]
     */
    public function get_alipay($data)
    {
        //处理平台自己的订单
        $order = Order::where(["order_sn"=>$data['order_sn']])->find();
        if (!$order) {
            return ApiReturn::r(0, [], '订单不存在');
        }
        if ($order['pay_status'] > 0) {
            return ApiReturn::r(0, [], '该订单支付状态已发生改变');
        }

        if ($order['pay_type'] =='wxpay') {
            return ApiReturn::r(0, [], '请使用发起微信支付接口');
        }

        if ($order['pay_type'] =='appleiap') {
            return ApiReturn::r(0, [], '请使用发起苹果支付接口');
        }

        $data = array(
            'subject' => Order::$orderTypes[$order['order_type']],
            'body' => Order::$orderTypes[$order['order_type']],
            'out_trade_no' => $order['order_sn'],
            'total_amount' => $order['payable_money'],
            'notify_url'=> config('web_site_domain').'/index/pay/ali_notify'
        );
        try {
            $string = addons_action('Alipay', 'Aop', 'AlipayTradeAppPayRequest', [$data]);
        } catch (\Exception $e) {
            return ApiReturn::r(0, [], '调取支付失败' . $e->getMessage());
        }

        if ($string) {
            return ApiReturn::r(1, $string, '请按照此配置调起支付');
        }
        return ApiReturn::r(0,[], '调取支付失败');
    }

    /**
     * 获取内购预支付订单
     * @param $data
     * @return mixed
     * @author 似水星辰 [ 2630481389@qq.com ]
     */
    public function get_iospay($data)
    {
        $order = Order::where("order_sn", $data['order_sn'])->find();
        if (!$order) {
            return ApiReturn::r(0, [], '订单不存在');
        }
        if ($order['pay_status'] > 0) {
            return ApiReturn::r(0, [], '该订单支付状态已发生改变');
        }
        if ($order['pay_type'] =='wxpay') {
            return ApiReturn::r(0, [], '请使用发起微信支付接口');
        }

        if ($order['pay_type'] =='alipay') {
            return ApiReturn::r(0, [], '请使用发起苹果支付接口');
        }
        if (!$order['app_name']) {
            return ApiReturn::r(0, [], '获取内购项目失败');
        }
        //使用INIT方法注入参数
        $data = [];
        $data['config'] = array(
            'pro_id' => $order['app_name'],
            'order_no' => $order['order_sn'],
        );
        try {
            $ios = addons_action('Iospay', 'App', 'init', [$data]);
            $arr = $ios->run();
        } catch (\Exception $e) {
            return ApiReturn::r(0, [], '调取支付失败' . $e->getMessage());
        }
        if ($arr) {
            return ApiReturn::r(1, $arr, '请按照此配置调起支付');
        }
        return ApiReturn::r(0, [], '调取支付失败');
    }
}