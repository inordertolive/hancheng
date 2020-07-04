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
namespace addons\WeChat\controller;

require_once __DIR__ . '/../sdk/Wxpay/WxPay.Api.php';
/**
 * Class AppPay 微信开放平台支付联调
 * @package plugins\WeChat\controller
 * @author 似水星辰 [ 2630481389@qq.com ]
 * @created 2019/9/9 22:07
 */
class AppPay extends Base {

    public function __construct(\think\Request $request = null) {
        parent::__construct($request);
        $this->setConfig('app');
    }

    //返回实例本身
    function init($config = null) {
        $this->setConfig('app', $config);
        return $this;
    }

    //支付一个订单 
    function pay($data) {
        $body = $data['body'];
        $total_fee = $data['total_fee'];
        $out_trade_no = $data['out_trade_no'];
        //组装订单数据 
        $body = preg_replace('/\r\n/', '', $body);
        $body = mb_strlen($body) > 40 ? mb_substr($body, 0, 40, 'utf-8') . '...' : $body;

        $time = time();
        $data = array(
            'body' => $body,
            'total_fee' => $total_fee * 100,
            'out_trade_no' => $out_trade_no,
            'startTime' => date("YmdHis", $time),
            'expireTime' => date("YmdHis", $time + 600),
            'timestamp' => $time,
            'notify_url' => $data['notify_url']
        );
        $result = $this->getPayUnifiedOrder($data, "APP"); //APP支付下单       

        if ($result['return_code'] == 'FAIL') {
            throw new \Exception($result['return_msg']);
        }

        if ($result['result_code'] == 'FAIL') {
            throw new \Exception($result['err_code'] . ':' . $result['err_code_des']);
        }
        $wechat = [
            'appid' => $result['appid'],
            'partnerid' => $result['mch_id'],
            'prepayid' => $result['prepay_id'],
            'package' => 'Sign=WXPay',
            'noncestr' => \WxPayApi::getNonceStr(),
            'timestamp' => time()
        ];
        $wechat['sign'] = $this->MakeSign($wechat);
        return $wechat;
    }

    //退款
    function backPay($out_trade_no, $total_fee, $refund_fee, $pay_sn) {

        $input = new \WxPayRefund();
        $input->SetOut_trade_no($out_trade_no);
        $input->SetTotal_fee($total_fee);
        $input->SetRefund_fee($refund_fee);
        $input->SetOut_refund_no($pay_sn);
        $input->SetOp_user_id(\WeixinConfig::getConfig('MCHID'));
        $result = \WxPayApi::refund($input);
        // dump($result);die;
        return $result['return_code'] == 'SUCCESS';
    }

    //退款状态查询
    function bcakQueryPay($out_refund_no) {
        $input = new WxPayRefundQuery();
        $input->SetOut_refund_no($out_refund_no);
        $result = WxPayApi::refundQuery($input);
        // dump($result);die;
        return $result['return_code'] == 'SUCCESS';
    }

    //同步回调
    function verifyReturn($out_trade_no) {

        $input = new \WxPayOrderQuery();
        $input->SetOut_trade_no($out_trade_no);
        $arr = \WxPayApi::orderQuery($input);
        if (!$arr) {
            throw new \Exception('订单查询失败');
        }
        if ($arr['return_code']  == 'FAIL') {
            throw new \Exception($arr['return_msg']);
        }
        if ($arr['result_code']  == 'FAIL') {
            throw new \Exception($arr['err_code'] . ':' . $arr['err_code_des']);
        }
        //此处到前端判断比较好
        //if($arr['trade_state'] != 'SUCCESS'){
        //	throw new \Exception($arr['trade_state_desc']);
        //}
        return $arr;
    }

    //异步回调
    function verifyNotify() {
        $notify = $this->WxPayNotify();
        $arr = $notify->Handle();
        if ($arr['return_code']  == 'FAIL') {
            throw new \Exception($arr['return_msg']);
        }
        if ($arr['result_code']  == 'FAIL') {
            throw new \Exception($arr['err_code'] . ':' . $arr['err_code_des']);
        }
        return $arr;
    }

    //打印内容给微信
    function NotifyProcess($result = false, $msg = '', $needSign = false) {
        return $this->WxPayNotify()->NotifyProcess($result, $msg, $needSign);
    }

    //私有方法实例化 异步类
    private function WxPayNotify() {
        require_once __DIR__ . '/../sdk/Wxpay/WxPay.Notify.php';
        static $notify = null;
        if (null === $notify)
            $notify = new \WxPayNotify();
        return $notify;
    }

    //调用统一下单
    private function getPayUnifiedOrder($data, $Trade_type) {
        $_SERVER['REMOTE_ADDR'] = \think\facade\Request::instance()->ip();
        $input = new \WxPayUnifiedOrder();
        $input->SetBody($data['body']);
        if (!empty($data['openid'])){
            $input->SetOpenid($data['openid']);
        }
        $notify = $data['notify_url'] ? $data['notify_url'] : \WeixinConfig::getConfig('NOTIFY_URL');
        $input->SetOut_trade_no($data['out_trade_no']); //订单号 
        $input->SetTotal_fee($data['total_fee']); //总价
        $input->SetTime_start($data['startTime']); //开始时间
        $input->SetTime_expire($data['expireTime']); //失效时间10分钟 
        $input->SetNotify_url($notify);
        $input->SetTrade_type($Trade_type);
        $input->SetProduct_id($data['out_trade_no']);
        $result = \WxPayApi::unifiedOrder($input);
        return $result;
    }

    //签名
    private function MakeSign($data) {
        //签名步骤一：按字典序排序参数
        ksort($data);
        $buff = "";
        foreach ($data as $k => $v) {
            if ($k != "sign" && $v != "" && !is_array($v)) {
                $buff .= $k . "=" . $v . "&";
            }
        }
        $string = trim($buff, "&");

        //签名步骤二：在string后加入KEY
        $string = $string . "&key=" . \WeixinConfig::getConfig('KEY');
        //签名步骤三：MD5加密
        $string = md5($string);
        //签名步骤四：所有字符转为大写
        $result = strtoupper($string);
        return $result;
    }

}
