<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<meta content="width=device-width,initial-scale=1.0,maximum-scale=1.0,user-scalable=no" name="viewport">
<meta content="yes" name="apple-mobile-web-app-capable">
<meta content="black" name="apple-mobile-web-app-status-bar-style">
<meta content="telephone=no" name="format-detection">
<meta content="email=no" name="format-detection">
<link rel="stylesheet" href="https://wepayui.github.io/project/pay_scan_code/css/wepayui.min.css">
<link rel="stylesheet" href="https://wepayui.github.io/project/pay_scan_code/css/index.css">
<title>扫码付款</title>
</head>
<!-- 
	通用说明： 
	1.模块的隐藏添加class:hide;
	2.body标签默认绑定ontouchstart事件，激活所有按钮的:active效果
-->
<body ontouchstart class="weui-wepay-code-wrap">
<div class="weui-wepay-code weui-wepay-code_logo">
    <div class="weui-wepay-code__bd">
        <p class="weui-wepay-code__logo">
            <img src="https://wepayui.github.io/project/pay_scan_code/img/wepay_logo_default_white_500x126.png" alt="" width="190" height="48;">
        </p>
        <div class="weui-wepay-code__main">
            <div class="weui-wepay-code__img">
                <img src="<?php echo $codeurl;?>" alt="" width="130" height="130">
            </div>
            <h2 class="weui-wepay-code__subtitle">扫码付款</h2>
        </div>
    </div>
    <div class="weui-wepay-code__ft">
        <div class="weui-wepay-code__business">
            <div class="weui-wepay-code__business-logo">
                河南盛装网络科技有限公司
            </div>
            <p class="weui-wepay-code__business-info">提供支持服务</p>
        </div>
    </div>
</div>
</body>
</html>
