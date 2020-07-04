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

namespace app\index\controller;

use think\Controller;
use app\common\model\Order;

/**
 * 支付回调
 * @author 似水星辰 [ 2630481389@qq.com ]
 */
class Pay extends Controller {

    /**
     * 支付宝异步回调
     * @author 似水星辰 [ 2630481389@qq.com ]
     * @created 2019/9/12 17:11
     */
    function ali_notify() {
        //注意旧版是Web,新版是Aop
        $alipay = addons_action('Alipay', 'Aop', 'init');
        try {
            $arr = $alipay->verifyNotify();
        } catch (\Exception $e) {
            $alipay->NotifyProcess(false, $e);
        }

        $order_no = $arr['out_trade_no'];
        $res = Order::verify($order_no, 'alipay', $arr['trade_no'], $arr['total_amount']);
        if(!$res){
            $alipay->NotifyProcess(false, 'ERROR');
        }
        $alipay->NotifyProcess(true);
    }

    /**
     * 支付宝同步回调
     * @author 似水星辰 [ 2630481389@qq.com ]
     */
    function ali_return() {

        try {
            //注意旧版是Web,新版是Aop
            $alipay = addons_action('Alipay', 'Aop', 'init');
            $arr = $alipay->verifyReturn();
        } catch (\Exception $e) {

            $this->error("支付失败");
        }

        $this->success("支付成功", '/index/recharge');
    }

    /**
     * IOS异步回调	
     * @author 晓风<215628355@qq.com>
     */
    function ios_notify() {
        try {
            $ios = addons_action('Iospay', 'App', 'init');
            $arr = $ios->verifyNotify();
        } catch (\Exception $e) {
            $ios->NotifyProcess(0, 'FAIL');
        }
        //商户流程	
        if ($arr['status'] == 0) {
            $order_no = $arr['out_trade_no'];
            $pay_type = $arr['production'] == 1 ? 'appleiap' : 'IosPayTest';
            Order::verify($order_no, $pay_type, $arr['transaction_id']) or $ios->NotifyProcess(0, 'error');
            $ios->NotifyProcess(1, 'success');
        }
        $ios->NotifyProcess(0, 'STATUS FAIL');
    }

    /**
     * 微信公众号同步查询
     * @author 晓风<215628355@qq.com>
     */
    function wxcode_return() {
        $order_no = input('order_no', '');
        if (!$order_no) {
            $this->error('订单号错误');
        }
        try {
            $weChat = addons_action('WeChat', 'CodePay', 'init');
            $arr = $weChat->verifyReturn($order_no);
        } catch (\Exception $e) {
            $this->error($e->getMessage());
        }

        if ($arr['trade_state'] == 'SUCCESS') {
            Order::verify($arr['out_trade_no'], 'WxCodePay', $arr['transaction_id']);
        }
        $this->success('支付成功', '', [
            'trade_state' => $arr['trade_state'],
            'trade_state_desc' => $arr['trade_state_desc'],
        ]);
    }

    /**
     * 微信公众号异步回调
     * @author 晓风<215628355@qq.com>
     */
    function wxcode_notify() {

        try {
            $weChat = addons_action('WeChat', 'CodePay', 'init');
            $arr = $weChat->verifyNotify();
        } catch (\Exception $e) {
            $weChat->NotifyProcess(false, '支付异常');
        }
        $order_no = $arr['out_trade_no'];
        //商户流程	
        Order::verify($order_no, 'wxpay', $arr['transaction_id']) or $weChat->NotifyProcess(false, '订单已支付或不存在');
        $weChat->NotifyProcess(true, 'OK');
    }

    /**
     * 微信APP异步回调
     * @author 晓风<215628355@qq.com>
     */
    function wxapp_notify() {
        $weChat = addons_action('WeChat', 'AppPay', 'init');
        try {
            $arr = $weChat->verifyNotify();
        } catch (\Exception $e) {
            $weChat->NotifyProcess(false, '支付异常');
        }
        //商户流程
        $order_no = $arr['out_trade_no'];
        $res = Order::verify($order_no, 'wxpay', $arr['transaction_id'], $arr['total_fee']);
        if(!$res){
            $weChat->NotifyProcess(false, '订单已支付或不存在');
        }
        $weChat->NotifyProcess(true, 'OK');
    }

}
