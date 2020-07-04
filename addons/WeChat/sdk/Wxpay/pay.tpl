<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<title>确认支付</title>
<meta content="width=device-width,initial-scale=1.0,maximum-scale=1.0,user-scalable=no" name="viewport">
<meta content="yes" name="apple-mobile-web-app-capable">
<meta content="black" name="apple-mobile-web-app-status-bar-style">
<meta content="telephone=no" name="format-detection">
<meta content="email=no" name="format-detection">
<link rel="stylesheet" href="https://act.weixin.qq.com/static/cdn/css/wepayui/0.1.1/wepayui.min.css">
<style>
<!--
body {background-color: #FFF;}
.info-area {
    position: relative;
    padding: 0 18px;
}
.info-area .totle {
    position: relative;
    padding: 25px 0 21px;
    text-align: center;
}
.info-area .totle .totle-title {
    font-size: 15px;
    color: #000;
}
.info-area .totle .totle-num {
    font-size: 50px;
    padding-top: 7px;
    text-align: center;
}
.hide{display:none}
-->
</style>

</head>
<body ontouchstart>


<div class="info-area">	
    <dl class="totle">
        <dt class="totle-title">
            <h2>微信支付</h2>
        </dt>				
        <dd class="totle-num">
            <strong>￥<?php echo $data['total_fee']/100 ;?></strong>				
        </dd>
    </dl>	
</div>
<div class="info-btn">
    <a href="javascript:;" class="weui-btn weui-btn_default weui-btn_loading" id="pay_loading"><i class="weui-loading"></i>正在支付</a>       
</div>
<div class="weui-wepay-logos weui-wepay-logos_ft">
   <img src="https://act.weixin.qq.com/static/cdn/img/wepayui/0.1.1/wepay_logo_default_gray.svg" alt="" height="16">
</div>

<!-- 支付联调]] -->
<script>
<!--
/*
function onBridgeReady(){
   WeixinJSBridge.invoke(
       'getBrandWCPayRequest', <?php echo json_encode($jsApi);?>,
       function(res){  
           alert(res.err_msg);       
           if(res.err_msg == "get_brand_wcpay_request:ok" ) {
               $("#pay_loading").hide();
               $("#pay_btn").removeClass('hide');
           }    
           // 使用以上方式判断前端返回,微信团队郑重提示：res.err_msg将在用户支付成功后返回    ok，但并不保证它绝对可靠。 
       }
   ); 
}
if (typeof WeixinJSBridge == "undefined"){
   if( document.addEventListener ){
       document.addEventListener('WeixinJSBridgeReady', onBridgeReady, false);
   }else if (document.attachEvent){
       document.attachEvent('WeixinJSBridgeReady', onBridgeReady); 
       document.attachEvent('onWeixinJSBridgeReady', onBridgeReady);
   }
}else{
   onBridgeReady();
}
*/
-->
</script>
<!--微信支付最新代码-->
<script type="text/javascript" charset="UTF-8" src="http://res.wx.qq.com/open/js/jweixin-1.0.0.js"></script>
<script type="text/javascript">

function pay(){	
	wx.config({
	    debug: false, // 开启调试模式,调用的所有api的返回值会在客户端alert出来，若要查看传入的参数，可以在pc端打开，参数信息会通过log打出，仅在pc端时才会打印。
	    appId: "<?php echo $config['appid'];?>", // 必填，公众号的唯一标识
	    timestamp:"<?php echo $config['timestamp'];?>" , // 必填，生成签名的时间戳
	    nonceStr: "<?php echo $config['noncestr'];?>", // 必填，生成签名的随机串
	    signature: "<?php echo $config['signature'];?>",// 必填，签名，见附录1
	    jsApiList: ['chooseWXPay'] // 必填，需要使用的JS接口列表，所有JS接口列表见附录2
	});
	wx.ready(function(){
	    // config信息验证后会执行ready方法，所有接口调用都必须在config接口获得结果之后，config是一个客户端的异步操作，所以如果需要在页面加载时就调用相关接口，则须把相关接口放在ready函数中调用来确保正确执行。对于用户触发时才调用的接口，则可以直接调用，不需要放在ready函数中。
		wx.chooseWXPay({
		    timestamp: "<?php echo $chooseWXPay['timeStamp'];?>", // 支付签名时间戳，注意微信jssdk中的所有使用timestamp字段均为小写。但最新版的支付后台生成签名使用的timeStamp字段名需大写其中的S字符
		    nonceStr: "<?php echo $chooseWXPay['nonceStr'];?>", // 支付签名随机串，不长于 32 位
		    package: "<?php echo $chooseWXPay['package'];?>", // 统一支付接口返回的prepay_id参数值，提交格式如：prepay_id=***）
		    signType: "<?php echo $chooseWXPay['signType'];?>", // 签名方式，默认为'SHA1'，使用新版支付需传入'MD5'
		    paySign: "<?php echo $chooseWXPay['paySign'];?>", // 支付签名
		    complete: function (res) {
		        // 支付成功后的回调函数             
		    	location.href='<?php echo $returnUrl ;?>';
		    }
		});
	});
}
window.onload = pay();
</script>  
</body>
</html>