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

require_once  __DIR__ .'/../sdk/Alipay.php';
/**
 * 支付宝网关支付相关业务.旧版本  新版本请使用AOP
 *
 * @author 晓风<215628355@qq.com>
 * @date 2018-6-23 13:40:58
 */
class Web extends Base
{
	
	//返回实例本身
	function init($config = null){
		if(is_array($config)){
			$this->config = array_merge($this->config,$config);
		}
		return $this;
	}
    /**
     * 支付宝下单获取网页支付入口
     * @author 晓风<215628355@qq.com>
     * @param string $object 产品名称
     * @param float $order_amt 支付总价
     * @param string $order_sn  订单号
    
     * @return html
     */
    public function run($object, $order_amt,$order_sn){       
        $alipay = new \Alipay($this->config);           
        //建立请求           
        return  $alipay->run($object,$order_amt,$order_sn);
    }
    
   
    /**
     * 支付宝同步回调验签 正确返回订单号
     * @author 晓风<215628355@qq.com>  
     * @return boolean|string
     */
    function verifyReturn(){          
         $alipay = new \Alipay($this->config);
         $verify_result = $alipay->verifyReturn();         
         if($verify_result) { 
            if($_GET['trade_status'] == 'TRADE_FINISHED' || $_GET['trade_status'] == 'TRADE_SUCCESS') {        
                return $_GET;              
            }            
        }  	
		throw new \Exception("VerifyReturn Check Sign Erroe!");		
  
    }  
     /**
     * 支付宝异步回调验签 正确返回订单号
     * @author 晓风<215628355@qq.com>   
     * @return boolean|string
     */  
    function verifyNotify(){
		
         //计算得出通知验证结果	
        $alipay = new \Alipay($this->config);
        $verify_result = $alipay->verifyNotify();
        if(!$verify_result) {
            if($_POST['trade_status'] == 'TRADE_FINISHED' || $_POST['trade_status'] == 'TRADE_SUCCESS') {			
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
