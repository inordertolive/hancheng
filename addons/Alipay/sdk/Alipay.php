<?php
/* *
 * 类名：AlipaySubmit
 * 功能：支付宝各接口请求提交类
 * 详细：构造支付宝各接口表单HTML文本，获取远程HTTP数据
 * 版本：3.3
 * 日期：2012-07-23
 * 说明：
 * 以下代码只是为了方便商户测试而提供的样例代码，商户可以根据自己网站的需要，按照技术文档编写,并非一定要使用该代码。
 * 该代码仅供学习和研究支付宝接口使用，只是提供一个参考。
 */
 
//支付宝联调 huliangming<215628355@qq.com> 2016年8月5日 18:41:06 重写
require_once __DIR__ . "/Alipay/AlipayFunction.class.php";

class Alipay {

	public $alipay_config;
	/**
	 *支付宝网关地址（新）
	 */
	public $alipay_gateway_new = 'https://mapi.alipay.com/gateway.do?';    
    
     /**
     * HTTPS形式消息验证地址
     */
	public $https_verify_url = 'https://mapi.alipay.com/gateway.do?service=notify_verify&';
	/**
     * HTTP形式消息验证地址
     */
	public $http_verify_url = 'http://notify.alipay.com/trade/notify_query.do?';


	function __construct($config=null){
	    $this->alipay_config = require_once __DIR__ . "/Alipay/Alipay.config.php";
            if(is_array($config)){            
                $this->alipay_config = array_merge($this->alipay_config,$config);
            }
	}
    //直接支付    
    function run($object,$total_fee,$out_trade_no){        
           
            $alipay_config = $this->alipay_config;
            $subject = $body = preg_replace('/\r\n/','',$object);
            $subject = mb_strlen($subject) > 40 ?  mb_substr($subject,0,40,'utf-8').'...' : $subject;              
            $parameter = array(
                    "service"            => $alipay_config['service'],
                    "partner"            => $alipay_config['partner'],
                    "seller_id"          => $alipay_config['seller_id'],
                    "payment_type"   	 => $alipay_config['payment_type'],
                    "notify_url"	 => $alipay_config['notify_url'],
                    "return_url"	 => $alipay_config['return_url'],                    
                    "anti_phishing_key"  => $alipay_config['anti_phishing_key'],
                    "exter_invoke_ip"    => $alipay_config['exter_invoke_ip'],
                    "out_trade_no"	 => $out_trade_no,
                    "subject"	         => $subject,
                    "total_fee"	         => $total_fee,
                    "app_pay"            => "Y",//尝试唤起APP
                    "body"	         => $body,
                    "_input_charset"	 => trim(strtolower($alipay_config['input_charset'])),
                    'extra_common_param' => ''                    
            );   
            //建立请求                 
           return $this->buildRequestForm($parameter,"get", "确认");
    }
	
	/**
	 * 生成签名结果
	 * @param $para_sort 已排序要签名的数组
	 * return 签名结果字符串
	 */
	function buildRequestMysign($para_sort) {
		//把数组所有元素，按照“参数=参数值”的模式用“&”字符拼接成字符串
		$prestr = AlipayFunction::createLinkstring($para_sort);
		
		$mysign = "";
		switch (strtoupper(trim($this->alipay_config['sign_type']))) {
			case "MD5" :
				$mysign = AlipayFunction::md5Sign($prestr, $this->alipay_config['key']);
				break;
			default :
				$mysign = "";
		}
		
		return $mysign;
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

		//生成签名结果
		$mysign = $this->buildRequestMysign($para_sort);
		
		//签名结果与签名方式加入请求提交参数组中
		$para_sort['sign'] = $mysign;
		$para_sort['sign_type'] = strtoupper(trim($this->alipay_config['sign_type']));
		
		return $para_sort;
	}

	/**
     * 生成要请求给支付宝的参数数组
     * @param $para_temp 请求前的参数数组
     * @return 要请求的参数数组字符串
     */
	function buildRequestParaToString($para_temp) {
		//待请求参数数组
		$para = $this->buildRequestPara($para_temp);
		
		//把参数组中所有元素，按照“参数=参数值”的模式用“&”字符拼接成字符串，并对字符串做urlencode编码
		$request_data =  AlipayFunction::createLinkstringUrlencode($para);
		
		return $request_data;
	}
	
    /**
     * 建立请求，以表单HTML形式构造（默认）
     * @param $para_temp 请求参数数组
     * @param $method 提交方式。两个值可选：post、get
     * @param $button_name 确认按钮显示文字
     * @return 提交表单HTML文本
     */
	function buildRequestForm($para_temp, $method, $button_name) {
		//待请求参数数组
		$para = $this->buildRequestPara($para_temp);
		
		$sHtml = "<form id='alipaysubmit' name='alipaysubmit' action='".$this->alipay_gateway_new."_input_charset=".trim(strtolower($this->alipay_config['input_charset']))."' method='".$method."'>";
		while (list ($key, $val) = each ($para)) {
            $sHtml.= "<input type='hidden' name='".$key."' value='".$val."'/>";
        }

		//submit按钮控件请不要含有name属性
        $sHtml = $sHtml."<input type='submit'  value='".$button_name."' style='display:none;'></form>";
		
		$sHtml = $sHtml."<script>document.forms['alipaysubmit'].submit();</script>";
		
		return $sHtml;
	}
	
	
	/**
     * 用于防钓鱼，调用接口query_timestamp来获取时间戳的处理函数
	 * 注意：该功能PHP5环境及以上支持，因此必须服务器、本地电脑中装有支持DOMDocument、SSL的PHP配置环境。建议本地调试时使用PHP开发软件
     * return 时间戳字符串
	 */
	function query_timestamp() {
		$url = $this->alipay_gateway_new."service=query_timestamp&partner=".trim(strtolower($this->alipay_config['partner']))."&_input_charset=".trim(strtolower($this->alipay_config['input_charset']));
		$encrypt_key = "";		

		$doc = new DOMDocument();
		$doc->load($url);
		$itemEncrypt_key = $doc->getElementsByTagName( "encrypt_key" );
		$encrypt_key = $itemEncrypt_key->item(0)->nodeValue;
		
		return $encrypt_key;
	}
    
    
    
    /**
     * 针对notify_url验证消息是否是支付宝发出的合法消息
     * @return 验证结果
     */
	function verifyNotify(){
		if(empty($_POST)) {//判断POST来的数组是否为空
			return false;
		}
		else {
			//生成签名结果
			$isSign = $this->getSignVeryfy($_POST, $_POST["sign"]);
			//获取支付宝远程服务器ATN结果（验证是否是支付宝发来的消息）
			$responseTxt = 'false';
			if (! empty($_POST["notify_id"])) {$responseTxt = $this->getResponse($_POST["notify_id"]);}
			
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
			if (preg_match("/true$/i",$responseTxt) && $isSign) {
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
	function verifyReturn(){
		if(empty($_GET)) {//判断POST来的数组是否为空
			return false;
		}
		else {
			//生成签名结果
			$isSign = $this->getSignVeryfy($_GET, $_GET["sign"]);
			//获取支付宝远程服务器ATN结果（验证是否是支付宝发来的消息）
			$responseTxt = 'false';
			if (! empty($_GET["notify_id"])) {$responseTxt = $this->getResponse($_GET["notify_id"]);}
			
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
			if (preg_match("/true$/i",$responseTxt) && $isSign) {
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
			case "MD5" :
				$isSgin = AlipayFunction::md5Verify($prestr, $sign, $this->alipay_config['key']);
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
		if($transport == 'https') {
			$veryfy_url = $this->https_verify_url;
		}
		else {
			$veryfy_url = $this->http_verify_url;
		}
		$veryfy_url = $veryfy_url."partner=" . $partner . "&notify_id=" . $notify_id;
		$responseTxt = AlipayFunction::getHttpResponseGET($veryfy_url, $this->alipay_config['cacert']);
		
		return $responseTxt;
	}
}
?>