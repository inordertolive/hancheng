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

require_once __DIR__ . '/../sdk/AlipayApp.php';

/**
 * APP支付组件.旧版本 新版本请使用AOP
 * @author 晓风<215628355@qq.com>
 * @package plugins\AliyunLive\controller
 */
class App extends Base {
	
	//返回实例本身
	function init($config = null){
		if(is_array($config)){
			$this->config = array_merge($this->config,$config);
		}
		return $this;
	}
    
    /**
     * 支付宝下单获取APP支付入口，返回签名好的数组
     * @author 晓风<215628355@qq.com>
     * @param string $object 产品名称
     * @param float $order_amt 支付总价
     * @param string $order_sn  订单号
     * @param array $config    自定义配置数组
     * @return html
     */
    public function run($object, $order_amt,$order_sn){       
        $AlipayApp = new \AlipayApp($this->config);           
        //建立请求           
        return  $AlipayApp->run($object,$order_amt,$order_sn);
    }


     /**
     * 支付宝异步回调验签 正确返回订单号
     * @author 晓风<215628355@qq.com>
     * @param array $config 自定义配置数组
     * @return boolean|string
     */  
    public function verifyNotify() {        
        //计算得出通知验证结果
        $AlipayApp = new \AlipayApp($this->config);
        $verify_result = $AlipayApp->verifyNotify();
        if ($verify_result) {
            if ($_POST['trade_status'] == 'TRADE_FINISHED' || $_POST['trade_status'] == 'TRADE_SUCCESS') {
                return $_POST;
            }
        }
        throw new \Exception("VerifyNotify Check Sign Erroe!");
    }
	
	
	//异步回调打印
	function NotifyProcess($result = false,$message = 'FAIL'){		
		if($result){
			die('success');
		}	
		die($message);
	}	

}
