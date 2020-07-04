<?php

/* *
 * 类名：AlipayNotify
 * 功能：支付宝通知处理类
 * 详细：处理支付宝各接口通知返回
 * 版本：3.2
 * 日期：2011-03-25
 * 说明：
 * 以下代码只是为了方便商户测试而提供的样例代码，商户可以根据自己网站的需要，按照技术文档编写,并非一定要使用该代码。
 * 该代码仅供学习和研究支付宝接口使用，只是提供一个参考

 * ************************注意*************************
 * 调试通知返回时，可查看或改写log日志的写入TXT里的数据，来检查通知返回是否正常
 */

require_once __DIR__ . "/AlipayApp/AlipayFunction.class.php";
require_once __DIR__ . "/AlipayApp/AlipayRsaFunction.class.php";

class AlipayApp {

    /**
     * HTTPS形式消息验证地址
     */
    var $https_verify_url = 'https://mapi.alipay.com/gateway.do?service=notify_verify&';

    /**
     * HTTP形式消息验证地址
     */
    var $http_verify_url = 'http://notify.alipay.com/trade/notify_query.do?';
    var $alipay_config;

    function __construct($config = null) {
        $this->alipay_config = require_once __DIR__ . "/AlipayApp/Alipay.config.php";
        if (is_array($config)) {
            $this->alipay_config = array_merge($this->alipay_config, $config);
        }
    }

    //获取支付信息    
    function run($object, $total_fee, $out_trade_no) {

        $alipay_config = $this->alipay_config;
        $subject = $body = preg_replace('/\r\n/', '', $object);
        $subject = mb_strlen($subject) > 40 ?  mb_substr($subject,0,40,'utf-8').'...' : $subject;  
        $parameter = array(		   
            "service" => $alipay_config['service'],
            "partner" => $alipay_config['partner'],
			"seller_id" => $alipay_config['seller_email'],
            "payment_type" => $alipay_config['payment_type'],
            "notify_url" => $alipay_config['app_notify_url'],
            "out_trade_no" => $out_trade_no,
            "subject" => $subject,
            "body"   => $body,
            "total_fee" => $total_fee,           
            "_input_charset" => trim(strtolower($alipay_config['input_charset'])),
        );        

        //建立请求                 
        return $this->buildRequestPara($parameter);
    }

    /**
     * 生成要请求给支付宝的参数数组
     * @param $para_temp 请求前的参数数组
     * @return 要请求的参数数组
     */
    function buildRequestPara($para_temp) {
        
        //除去待签名参数数组中的空值和签名参数
        $para_filter = AlipayFunction::paraFilter($para_temp);

        //对待签名参数数组排序
        $para_sort = AlipayFunction::argSort($para_filter);
        
        //把数组所有元素，按照'参数="参数值"'的模式用“&”字符拼接成字符串
        $prestr = AlipayFunction::createLinkstringToPay($para_sort);   
       // echo $prestr;die;
        //生成签名结果
        $mysign =  AlipayRsaFunction::rsaSign($prestr,trim($this->alipay_config['private_key_app_path']));

        //签名结果与签名方式加入请求提交参数组中
        $para_sort['sign']      = urlencode($mysign);
        $para_sort['sign_type'] = strtoupper(trim($this->alipay_config['sign_type']));	
        //返回的_string可以直接用于 支付宝的orderStr 直接唤醒支付		
		$para_sort['_string'] =  AlipayFunction::createLinkstringToPay($para_sort);		
        return $para_sort;      
    }

    /**
     * 针对notify_url验证消息是否是支付宝发出的合法消息
     * @return 验证结果
     */
    function verifyNotify() {
        if (empty($_POST)) {//判断POST来的数组是否为空
            return false;
        } else {
            //生成签名结果
            $isSign = $this->getSignVeryfy($_POST, $_POST["sign"]);
            //获取支付宝远程服务器ATN结果（验证是否是支付宝发来的消息）
            $responseTxt = 'false';
            if (!empty($_POST["notify_id"])) {
                $responseTxt = $this->getResponse($_POST["notify_id"]);
            }

            //写日志记录
            //if ($isSign) {
            //	$isSignStr = 'true';
            //}
            //else {
            //	$isSignStr = 'false';
            //}
            //$log_text = "responseTxt=".$responseTxt."\n notify_url_log:isSign=".$isSignStr.",";
            //$log_text = $log_text.createLinkString($_POST);
            //logResult($log_text);
            //验证
            //$responsetTxt的结果不是true，与服务器设置问题、合作身份者ID、notify_id一分钟失效有关
            //isSign的结果不是true，与安全校验码、请求时的参数格式（如：带自定义参数等）、编码格式有关
            if (preg_match("/true$/i", $responseTxt) && $isSign) {
                return true;
            } else {
                return false;
            }
        }
    }

    /**
     * 针对return_url验证消息是否是支付宝发出的合法消息
     * @return 验证结果
     */
    function verifyReturn() {
        if (empty($_GET)) {//判断POST来的数组是否为空
            return false;
        } else {
            //生成签名结果
            $isSign = $this->getSignVeryfy($_GET, $_GET["sign"]);
            //获取支付宝远程服务器ATN结果（验证是否是支付宝发来的消息）
            $responseTxt = 'false';
            if (!empty($_GET["notify_id"])) {
                $responseTxt = $this->getResponse($_GET["notify_id"]);
            }

            //写日志记录
            //if ($isSign) {
            //	$isSignStr = 'true';
            //}
            //else {
            //	$isSignStr = 'false';
            //}
            //$log_text = "responseTxt=".$responseTxt."\n return_url_log:isSign=".$isSignStr.",";
            //$log_text = $log_text.createLinkString($_GET);
            //logResult($log_text);
            //验证
            //$responsetTxt的结果不是true，与服务器设置问题、合作身份者ID、notify_id一分钟失效有关
            //isSign的结果不是true，与安全校验码、请求时的参数格式（如：带自定义参数等）、编码格式有关
            if (preg_match("/true$/i", $responseTxt) && $isSign) {
                return true;
            } else {
                return false;
            }
        }
    }

    /**
     * 获取返回时的签名验证结果
     * @param $para_temp 通知返回来的参数数组
     * @param $sign 返回的签名结果
     * @return 签名验证结果
     */
    function getSignVeryfy($para_temp, $sign) {
        //除去待签名参数数组中的空值和签名参数
        $para_filter = AlipayFunction::paraFilter($para_temp);

        //对待签名参数数组排序
        $para_sort = AlipayFunction::argSort($para_filter);

        //把数组所有元素，按照“参数=参数值”的模式用“&”字符拼接成字符串
        $prestr = AlipayFunction::createLinkstring($para_sort);

        $isSgin = false;
        switch (strtoupper(trim($this->alipay_config['sign_type']))) {
            case "RSA" :
                $isSgin = AlipayRsaFunction::rsaVerify($prestr, trim($this->alipay_config['ali_public_key_path']), $sign);
                break;
            default :
                $isSgin = false;
        }

        return $isSgin;
    }

    /**
     * 获取远程服务器ATN结果,验证返回URL
     * @param $notify_id 通知校验ID
     * @return 服务器ATN结果
     * 验证结果集：
     * invalid命令参数不对 出现这个错误，请检测返回处理中partner和key是否为空 
     * true 返回正确信息
     * false 请检查防火墙或者是服务器阻止端口问题以及验证时间是否超过一分钟
     */
    function getResponse($notify_id) {
        $transport = strtolower(trim($this->alipay_config['transport']));
        $partner = trim($this->alipay_config['partner']);
        $veryfy_url = '';
        if ($transport == 'https') {
            $veryfy_url = $this->https_verify_url;
        } else {
            $veryfy_url = $this->http_verify_url;
        }
        $veryfy_url = $veryfy_url . "partner=" . $partner . "&notify_id=" . $notify_id;
        $responseTxt = AlipayFunction::getHttpResponseGET($veryfy_url, $this->alipay_config['cacert']);

        return $responseTxt;
    }

}
