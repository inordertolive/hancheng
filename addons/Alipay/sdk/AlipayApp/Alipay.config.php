<?php
/* *
 * 配置文件
 * 版本：3.3
 * 日期：2012-07-19
 * 说明：
 * 以下代码只是为了方便商户测试而提供的样例代码，商户可以根据自己网站的需要，按照技术文档编写,并非一定要使用该代码。
 * 该代码仅供学习和研究支付宝接口使用，只是提供一个参考。
	
 * 提示：如何获取安全校验码和合作身份者id
 * 1.用您的签约支付宝账号登录支付宝网站(www.alipay.com)
 * 2.点击“商家服务”(https://b.alipay.com/order/myorder.htm)
 * 3.点击“查询合作者身份(pid)”、“查询安全校验码(key)”
	
 * 安全校验码查看时，输入支付密码后，页面呈灰色的现象，怎么办？
 * 解决方法：
 * 1、检查浏览器配置，不让浏览器做弹框屏蔽设置
 * 2、更换浏览器或电脑，重新登录查询。
 */
 
//↓↓↓↓↓↓↓↓↓↓请在这里配置您的基本信息↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓

return array(
    'appid'               => '',
    //接口名称，固定值。
    'service'             =>'mobile.securitypay.pay',
	//合作身份者id，以2088开头的16位纯数字
	'partner'		=> '',//
	
	 //收款支付宝账号，以2088开头由16位纯数字组成的字符串，一般情况下收款账号就是签约账号
    'seller_id' => '',

	//商户的私钥（后缀是.pem）文件相对路径
	'private_key_path'	=> '',
	
	//商户的私钥（后缀是.pem）文件相对路径
	'private_key_app_path'	=> '',

	//支付宝公钥（后缀是.pem）文件相对路径
	'ali_public_key_path'=> '',
    
    'app_notify_url'=>'',
    'payment_type' => '1',


	//签名方式 不需修改
	'sign_type'    => strtoupper('RSA'),

	//字符编码格式 目前支持 gbk 或 utf-8
	'input_charset'=> strtolower('utf-8'),

	//ca证书路径地址，用于curl中ssl校验
	//请保证cacert.pem文件在当前文件夹目录中
	'cacert'    =>  __DIR__ .'/cacert.pem',

	//访问模式,根据自己的服务器是否支持ssl访问，若支持请选择https；若不支持请选择http
	'transport'    => 'http',

);






