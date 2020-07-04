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

use app\common\controller\Common;
require_once __DIR__ .'/../sdk/WeixinConfig.php';
/**
 * 设置配置参数
 * @author 晓风<215628355@qq.com>
 */
class Base extends Common {

    public $config;
    public function __construct(\think\Request $request = null) {
        parent::__construct($request);         
        $config =  addons_config('Wechat');
        $configs = [];
        foreach($config as $key=>$val){
            $arr  = explode('_',$key);
            $prefix = $arr[0];
            unset($arr[0]);
            $name = implode('_',$arr);
            $name = strtoupper($name);
            $configs[$prefix][$name] = $val;            
        }
        $this->config = $configs;       
    }
    //获取后台配置参数
    private  function getSysTemConfig($preFix = 'code'){
        $config = $this->config[$preFix] ?? null;
        if(!$config){
            throw new \Exception("获取参数失败");  
        }        
        if($config['SSLCERT_PATH']){            
            $fileName = md5($config['SSLCERT_PATH']);            
            $path = dirname(__DIR__) .'/cert/' . $fileName . '.pem';
            if(!file_exists($path)){
                try{
                    file_put_contents($path,$config['SSLCERT_PATH']);                    
                }catch(\Exception $e){                    
                    throw new \Exception("公钥文件写入失败");                
                }
            }
           $config['SSLCERT_PATH'] = $path;
        }        
        if($config['SSLKEY_PATH']){            
            $fileName = md5($config['SSLKEY_PATH']);            
            $path =  dirname(__DIR__) .'/cert/' . $fileName . '.pem';
            if(!file_exists($path)){
                try{
                    file_put_contents($path, $config['SSLKEY_PATH']);                    
                }catch(\Exception $e){                    
                    throw new \Exception("私钥文件写入失败");                
                }
            }
            $config['SSLKEY_PATH'] = $path;
        }  
        return $config;
    }
    //获取指定配置
    protected function getConfig($name) 
    {
        static $config = [];
        if (!isset($config[$name])) {
            $file = __DIR__ . '/../sdk/config/' . $name . '.php';
            if (file_exists($file)) {
                $config[$name] = include $file;
            } else {
                throw new \Exception("缺少" . $name . "参数配置文件" . $file);
            }
            $con = $this->getSysTemConfig($name);
            $config[$name] = array_merge( $config[$name],$con);
        }
        return $config[$name];
    }
    //设置下文参数
    protected function setConfig($name, $config = null) {     
        $configs = $this->getConfig($name);
        if (is_array($config)) {
            $configs = array_merge($configs,$config);
        }
        \WeixinConfig::$config = $configs;
    }

}
