<?php
return [
	//['text', 'cod_token', '公众号配置-自定义TOKEN','需要做微信下发通知接口才可以配置'],
	//['text', 'code_encoding_aes_key', '公众号配置-ENCODING_AES_KEY','需要做微信下发通知接口才可以配置'],
	//['text', 'code_appid', '公众号配置-APPID','同时也是绑定支付的APPID（必须配置，开户邮件中可查看）'],
	//['text', 'code_appsecret', '公众号配置-APPSECRET','仅JSAPI支付的时候需要配置，登录公众平台，进入开发者中心可设置'],
	//['text', 'code_mchid', '公众号配置-商户号MCHID','必须配置，开户邮件中可查看'],
	//['text', 'code_key', '公众号配置-商户支付密钥KEY','参考开户邮件设置（必须配置，登录商户平台自行设置）'],
	//['text', 'code_notify_url', '公众号配置-支付异步回调地址','也可以在程序中动态配置'],
	//['text', 'code_return_url', '公众号配置-支付同步回调地址','必须配置'],
	//['textarea', 'code_sslcert_path', '公众号配置-支付商户证书CERT内容','仅退款时用'],
	//['textarea', 'code_sslkey_path', '公众号配置-支付商户证书KEY内容','仅退款时用'],
	
	['type'=>'text', 'name'=> 'app_appid', 'title'=>'APP配置-APPID','tips'=>'同时也是绑定支付的APPID（必须配置，开户邮件中可查看）'],	
	['type'=>'text', 'name'=> 'app_appsecret', 'title'=>'APP配置-APPSECRET','tips'=>'请登录开放平台进行配置'],	
	['type'=>'text', 'name'=> 'app_mchid', 'title'=>'APP配置-商户号MCHID','tips'=>'必须配置，开户邮件中可查看'],
	['type'=>'text', 'name'=> 'app_key', 'title'=>'APP配置-商户支付密钥KEY','tips'=>'参考开户邮件设置（必须配置，登录商户平台自行设置）'],
	['type'=>'text', 'name'=> 'app_notify_url', 'title'=>'APP配置-支付异步回调地址','tips'=>'也可以在程序中动态配置'],
	['type'=>'textarea', 'name'=> 'app_sslcert_path', 'title'=>'APP配置-支付商户证书CERT内容','tips'=>'仅退款时用'],
	['type'=>'textarea', 'name'=> 'app_sslkey_path', 'title'=>'APP配置-支付商户证书KEY内容','tips'=>'仅退款时用'],
	
	//['text', 'web_appid', 'WEB配置-APPID','在开放平台申请网站应用可以获得,用于网页版扫码登录'],
	//['text', 'web_appsecret', 'WEB配置-APPSECRET','在开放平台申请网站应用可以获得，用于网页版扫码登录'],
	
];
