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

class CodePay extends AuthCode {

    //支付一个订单 
    function jsApiPay($body, $total_fee, $out_trade_no, $openid = 0) {
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
            'openid' => $openid,
            'timestamp' => $time
        );
        $result = $this->getPayUnifiedOrder($data, "JSAPI"); //JSAPI支付下单    

        if ($result['return_code'] == 'FAIL') {
            throw new \Exception($result['return_msg']);
        }

        if ($result['result_code'] == 'FAIL') {
            throw new \Exception($result['err_code'] . ':' . $result['err_code_des']);
        }
        //获取JSAPI票据
        $config = $this->getJsapiConfig();
        if (!$config) {
            throw new \Exception('获取JSAPI授权失败');
        }
        //生产JSAPI唤醒
        $chooseWXPay = $this->getJsApi($result);
        return [
            'data' => $data,
            'config' => $config,
            'chooseWXPay' => $chooseWXPay,
        ];
    }

    //支付一个扫码支付订单返回，二维码地址 
    function codePay($body, $total_fee, $out_trade_no) {
        //组装订单数据 
        $body = mb_substr(preg_replace('/\r\n/', '', $body), 0, 30, 'utf-8') . '...';
        $time = time();
        $data = array(
            'body' => $body,
            'total_fee' => $total_fee * 100,
            'out_trade_no' => $out_trade_no,
            'startTime' => date("YmdHis", $time),
            'expireTime' => date("YmdHis", $time + 600),
            'openid' => null,
            'timestamp' => $time
        );

        $result = $this->getPayUnifiedOrder($data, "NATIVE"); //扫码支付下单
        if ($result['return_code'] == 'FAIL') {
            throw new \Exception($result['return_msg']);
        }
        if ($result['result_code'] == 'FAIL') {
            throw new \Exception($result['err_code'] . ':' . $result['err_code_des']);
        }
        return $result["code_url"];
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
        $input = new \WxPayRefundQuery();
        $input->SetOut_refund_no($out_refund_no);
        $result = \ WxPayApi::refundQuery($input);
        // dump($result);die;
        return $result['return_code'] == 'SUCCESS';
    }
    /**
     * 企业转账
     *   1、商户号（或同主体其他非服务商商户号）已入驻90日
     *   2、商户号（或同主体其他非服务商商户号）有30天连续正常交易
     *   3、 登录微信支付商户平台-产品中心，开通企业付款。
     * @param type $openid
     * @param type $money
     * @param type $true_name
     * @return type
     */
    public function transfers($openid,$amount,$partner_trade_no,$re_user_name,$desc){ 	
         //封装成数据        
        $wxpayData = new \WxPayData();
        $wxpayData->SetData("openid",$openid);
        $wxpayData->SetData("amount",  $amount * 100);
        $wxpayData->SetData("partner_trade_no",$partner_trade_no);
         //校验用户姓名选项，
         //NO_CHECK：不校验真实姓名 
         //FORCE_CHECK：强校验真实姓名（未实名认证的用户会校验失败，无法转账）
         //OPTION_CHECK：针对已实名认证的用户才校验真实姓名（未实名认证用户不校验，可以转账成功）
        $wxpayData->SetData("check_name","NO_CHECK");
        $wxpayData->SetData("re_user_name",$re_user_name);
        $wxpayData->SetData("desc",$desc);               
        $result =  \WxPayApi::transfers($wxpayData);
        if ($result['return_code'] == 'FAIL') {
            throw new \Exception($result['return_msg']);
        }
        if ($result['result_code'] == 'FAIL') {
            throw new \Exception($result['err_code'] . ':' . $result['err_code_des']);
        }
        return $result;        
    }
    //同步回调
    function verifyReturn($out_trade_no) {

        $input = new \WxPayOrderQuery();
        $input->SetOut_trade_no($out_trade_no);
        $arr = \WxPayApi::orderQuery($input);
        if (!$arr) {
            throw new \Exception('订单查询失败');
        }
        if ($arr['return_code'] != 'SUCCESS') {
            throw new \Exception($arr['return_msg']);
        }
        if ($arr['result_code'] != 'SUCCESS') {
            throw new \Exception($arr['err_code'] . $arr['err_code_des']);
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
        if ($arr['return_code'] != 'SUCCESS') {
            throw new \Exception($arr['return_msg']);
        }
        if ($arr['result_code'] != 'SUCCESS') {
            throw new \Exception($arr['err_code'] . $arr['err_code_des']);
        }
        //if($arr['trade_state'] != 'SUCCESS'){
        //    throw new \Exception($arr['trade_state_desc']);
       // }
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
        $_SERVER['REMOTE_ADDR'] = \think\Request::instance()->ip();
        $input = new \WxPayUnifiedOrder();
        $input->SetBody($data['body']);
        if ($data['openid'])
            $input->SetOpenid($data['openid']);
        $input->SetOut_trade_no($data['out_trade_no']); //订单号 
        $input->SetTotal_fee($data['total_fee']); //总价
        $input->SetTime_start($data['startTime']); //开始时间
        $input->SetTime_expire($data['expireTime']); //失效时间10分钟 
        $input->SetNotify_url(\WeixinConfig::getConfig('NOTIFY_URL'));
        $input->SetTrade_type($Trade_type);
        $input->SetProduct_id($data['out_trade_no']);
        $result = \WxPayApi::unifiedOrder($input);
        return $result;
    }   

    //获取JSAPI支付参数
    private function getJsApi($result) {
        $url = array(
            'appId' => $result['appid'],
            'timeStamp' => time(),
            'nonceStr' => $result['nonce_str'],
            'package' => 'prepay_id=' . $result['prepay_id'],
            'signType' => 'MD5'
        );      
        $url['paySign'] = $this->getSign($url);
        return $url;
    }
    //获得签名
    private function getSign($data){
	ksort($data); //字典排序
        $buff = "";
        foreach ($data as $k => $v) {
            if ($k != "sign" && $v != "" && !is_array($v)) {
                $buff .= $k . "=" . $v . "&";
            }
        }
        $buff = trim($buff, "&");
        return  strtoupper(md5($buff . '&key=' . \WeixinConfig::getConfig('KEY')));
    }  
}
