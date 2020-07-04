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

use app\common\controller\Common;
/**
 * Description of Base
 * @author 晓风<215628355@qq.com>
 */
class Base extends Common{    
    
    public $config;
       
    public function __construct(\think\Request $request = null) {
        parent::__construct($request);
        $this->config =  addons_config('Alipay');
        if(isset($this->config['ali_public_key_path'])){
            $fileName = md5($this->config['ali_public_key_path']);            
            $path = __DIR__ .'/../cert/' . $fileName . '.pem';
            if(!file_exists($path)){
                try{
                    file_put_contents($path, $this->config['ali_public_key_path']);                    
                }catch(\Exception $e){                    
                    throw new \Exception("公钥文件写入失败");                
                }
            }
            $this->config['ali_public_key_path'] = $path;
        }
		
		if(isset($this->config['private_key_app_path'])){
            $fileName = md5($this->config['private_key_app_path']);            
            $path = __DIR__ .'/../cert/' . $fileName . '.pem';
            if(!file_exists($path)){
                try{
                    file_put_contents($path, $this->config['private_key_app_path']);                    
                }catch(\Exception $e){                    
                    throw new \Exception("私钥文件写入失败");                
                }
            }
            $this->config['private_key_app_path'] = $path;
        }
    }
    
    
}
