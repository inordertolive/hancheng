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
namespace addons\Alipay\controller;

require_once __DIR__ . '/../sdk/Aop/AopClient.php';

/**
 * 支付宝aop接口
 * @author 晓风<215628355@qq.com>
 * @package plugins\AliyunLive\controller
 */
class Aop extends Base {

    /**
     * 支付宝API地址（新）
     */
    public $gatewayUrl = 'https://openapi.alipay.com/gateway.do';

    //返回实例本身
    function init($config = null) {


        if (is_array($config)) {
            $this->config = array_merge($this->config, $config);
        }
        return $this;
    }

    //直接载入Rrequest
    function loadRrequest($name) {
        $names = explode(".", $name);
        foreach ($names as &$val) {
            $val = ucfirst($val);
        }
        $name = implode('', $names);
        require_once __DIR__ . '/../sdk/Aop/request/' . $name . '.php';
    }

    /* 支付宝转账
     * @param $payee_account     支付宝账号
     * @param $amount        	 转账金额
     * @param $out_trade_no     订单号
     * @param $alipay_username  支付宝用户名
     * @param $payer_show_name  商户名称
     * @param $remark           备注
     * @return array
     */

    public function AlipayFundTransToaccountTransferRequest($payee_account, $amount, $out_trade_no, $alipay_username, $payer_show_name, $remark) {
        $this->loadRrequest("AlipayFundTransToaccountTransferRequest");
        $aop = new \AopClient();
        $aop->gatewayUrl = $this->gatewayUrl;
        $aop->appId = $this->config['appid'];
        $aop->rsaPrivateKey = $this->config['private_key'];
        $aop->alipayrsaPublicKey = $this->config['public_key'];
        $aop->apiVersion = '1.0';
        $aop->signType = 'RSA2';
        $aop->postCharset = 'UTF-8';
        $aop->format = 'json';
        $request = new \AlipayFundTransToaccountTransferRequest();
        //传入业务参数
        $bizcontent = json_encode([
            'out_biz_no' => $out_trade_no,
            'payee_type' => 'ALIPAY_LOGONID',
            'payee_account' => $payee_account,
            'amount' => $amount,
            'payer_show_name' => $payer_show_name,
            'payee_real_name' => $alipay_username,
            'remark' => $remark
        ]);
        $request->setBizContent($bizcontent);
        $result = $aop->execute($request);
        $sign = $result->sign;
        $responseNode = str_replace(".", "_", $request->getApiMethodName()) . "_response";
        $result = (array) $result->$responseNode;

        if (!empty($result['code']) && $result['code'] == 10000) {
            //msg //order_id
            return $result;
        }
        $error_msg = $result['sub_msg'] ?? ( $result['sub_msg'] ?? '未知错误');
        throw new \Exception($error_msg);
    }


    /**
     * app支付下单
     * @param $data
     * @author 似水星辰 [ 2630481389@qq.com ]
     * @created 2019/9/12 17:02
     */
    public function AlipayTradeAppPayRequest($data) {
        $subject=$data['subject'];
        $body=$data['body'];
        $out_trade_no=$data['out_trade_no'];
        $total_amount=$data['total_amount'];
        $this->loadRrequest("AlipayTradeAppPayRequest");
        $aop = new \AopClient;
        $aop->gatewayUrl = $this->gatewayUrl;
        $aop->appId = $this->config['appid'];
        $aop->rsaPrivateKey = $this->config['private_key'];
        $aop->format = "json";
        $aop->charset = "UTF-8";
        $aop->signType = "RSA2";
        $aop->alipayrsaPublicKey = $this->config['public_key'];
        //实例化具体API对应的request类,类名称和接口名称对应,当前调用接口名称：alipay.trade.app.pay
        $request = new \AlipayTradeAppPayRequest();

        //SDK已经封装掉了公共参数，这里只需要传入业务参数
        $bizcontent = json_encode([
            'body' => $body,
            'subject' => $subject,
            'out_trade_no' => $out_trade_no,
            'timeout_express' => '30m',
            'total_amount' => strval($total_amount),
            'product_code' => 'QUICK_MSECURITY_PAY',
        ]);
        $notify_url = $data['notify_url'] ? $data['notify_url'] : $this->config['app_notify_url'];
        $request->setNotifyUrl($notify_url); //异步回调地址
        $request->setBizContent($bizcontent);
        //这里和普通的接口调用不同，使用的是sdkExecute
        $response = $aop->sdkExecute($request);
        //htmlspecialchars是为了输出到页面时防止被浏览器将关键参数html转义，实际打印到日志以及http传输不会有这个问题
        return $response; //就是orderString 可以直接给客户端请求，无需再做处理。\
    }

    /**
     * 支付同步回调统一验签，电脑网站支付
     * 由于同步回调不可靠性，不可依赖此方式处理业务
     * @author 似水星辰 [ 2630481389@qq.com ]
     * @created 2019/9/12 17:03
     */
    public function verifyReturn() {

        $aop = new \AopClient;
        $aop->alipayrsaPublicKey = $this->config['public_key'];
        $verify_result = $aop->rsaCheckV1($_GET, NULL, "RSA2");

        if ($verify_result) {
            //同步回调不会返回支付状态
            //if ($_GET['trade_status'] == 'TRADE_FINISHED' || $_GET['trade_status'] == 'TRADE_SUCCESS') {
            //  return $_GET;
            //}
            //这里直接返回空数组，方便业务判断
            return [];
        }

        throw new \Exception("VerifyReturn Check Sign Erroe!");
    }


    /**
     * 支付异步回调统一验签，电脑网站支付和APP支付一样
     * @author 似水星辰 [ 2630481389@qq.com ]
     * @created 2019/9/12 17:03
     */
    public function verifyNotify() {
        $aop = new \AopClient;
        $aop->alipayrsaPublicKey = $this->config['public_key'];
        $verify_result = $aop->rsaCheckV1($_POST, NULL, "RSA2");
        if ($verify_result) {
            if ($_POST['trade_status'] == 'TRADE_FINISHED' || $_POST['trade_status'] == 'TRADE_SUCCESS') {
                return $_POST;
            }
        }
        throw new \Exception("VerifyNotify Check Sign Erroe!");
    }


    /**
     * 异步回调打印
     * @param bool $result
     * @param string $message
     * @author 似水星辰 [ 2630481389@qq.com ]
     * @created 2019/9/12 17:03
     */
    function NotifyProcess($result = false, $message = 'FAIL') {
        ob_end_clean();
        if ($result) {
            die('success');
        }
        \think\facade\Log::record($message);
        die($message);
    }

    //电脑网站支付
    //支付文档地址 https://docs.open.alipay.com/270/105899/
    public function AlipayTradePagePayRequest($subject, $body, $out_trade_no, $total_amount) {

        $this->loadRrequest("AlipayTradePagePayRequest");
        //构造参数  
        $aop = new \AopClient ();
        $aop->gatewayUrl = $this->gatewayUrl;
        $aop->appId = $this->config['appid'];
        $aop->rsaPrivateKey = $this->config['private_key'];
        $aop->apiVersion = '1.0';
        $aop->signType = 'RSA2';
        $aop->postCharset = 'utf-8';
        $aop->format = 'json';
        $request = new \AlipayTradePagePayRequest ();
        $request->setReturnUrl($this->config['return_url']);  //同步回调地址
        $request->setNotifyUrl($this->config['notify_url']);  //异步回调地址
        //SDK已经封装掉了公共参数，这里只需要传入业务参数
        $bizcontent = json_encode([
            'body' => $body,
            'subject' => $subject,
            'out_trade_no' => $out_trade_no,
            'timeout_express' => '30m',
            'total_amount' => strval($total_amount),
            'product_code' => 'FAST_INSTANT_TRADE_PAY',
        ]);
        $request->setBizContent($bizcontent);
        //请求  
        return $aop->pageExecute($request);
    }

    //手机网站支付
    //支付文档地址 https://docs.open.alipay.com/203/107090/
    public function AlipayTradeWapPayRequest($subject, $body, $out_trade_no, $total_amount) {

        $this->loadRrequest("AlipayTradeWapPayRequest");
        //构造参数  
        $aop = new \AopClient ();
        $aop->gatewayUrl = $this->gatewayUrl;
        $aop->appId = $this->config['appid'];
        $aop->rsaPrivateKey = $this->config['private_key'];
        $aop->alipayrsaPublicKey = $this->config['public_key'];
        $aop->apiVersion = '1.0';
        $aop->postCharset = 'utf-8';
        $aop->format = 'json';
        $aop->signType = 'RSA2';
        $request = new \AlipayTradeWapPayRequest();
        $request->setReturnUrl($this->config['return_url']);  //同步回调地址
        $request->setNotifyUrl($this->config['notify_url']);  //异步回调地址
        //SDK已经封装掉了公共参数，这里只需要传入业务参数
        $bizcontent = json_encode([
            'body' => $body,
            'subject' => $subject,
            'out_trade_no' => $out_trade_no,
            'timeout_express' => '30m',
            'total_amount' => strval($total_amount),
            'product_code' => 'QUICK_WAP_WAY',
        ]);
        $request->setBizContent($bizcontent);
        //请求  
        return $aop->pageExecute($request);
    }

}
