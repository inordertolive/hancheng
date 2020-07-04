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

//微信登录 分享统一调度
//author huliangming<215628355@qq.com>
class Web extends Base {
    
    public function __construct(\think\Request $request = null) {
        parent::__construct($request);
        $this->setConfig('web');
    }
    
	//返回实例本身
	function init($config = null){
		$this->setConfig('web',$config);
		return $this;
	}

    //获取扫码登录地址,返回一个URL
    //https://mp.weixin.qq.com/wiki?t=resource/res_main&id=mp1455784140&token=&lang=zh_CN
    public function getCodeLoginUrl($redirect_uri, $state = 1) {
        
        $url = 'https://open.weixin.qq.com/connect/qrconnect?';
        $arr = array(
            'appid' => \WeixinConfig::getConfig('APPID'),
            'redirect_uri' => $redirect_uri,
            'response_type' => 'code',
            'scope' => 'snsapi_login',
            'state' => $state,
        );
        return $url . http_build_query($arr) . '#wechat_redirect';
    }

    //微信授权回调,获取用户access_token和 OPENID,unionid，用getCodeLoginUrl回调的CODE
    public function getOpenid($code, $config = null) {        
        $url = 'https://api.weixin.qq.com/sns/oauth2/access_token?';
        $arr = array(
            'appid' => \WeixinConfig::getConfig('APPID'),
            'secret' => \WeixinConfig::getConfig('APPSECRET'),
            'code' => $code,
            'grant_type' => 'authorization_code',
        );
        $url .= http_build_query($arr);
        $json = $this->curl($url);
        $arr = json_decode($json, true);
        if (!empty($arr['openid'])) {
            return $arr;
        }
        $arr['errmsg'] = $arr['errmsg'] ?? '未知';
        throw new \Exception("错误异常," . $arr['errmsg']);
    }

    //获取用户信息
    //access_token 用户的 access_token 
    public function getUser($access_token, $openid) {
      
        $url = 'https://api.weixin.qq.com/sns/userinfo?';
        $arr = array(
            'access_token' => $access_token,
            'openid' => $openid,
            'lang' => 'zh_CN',
        );
        $url .= http_build_query($arr);
        $json = $this->curl($url);
        $arr = json_decode($json, true);
        if (!empty($arr['openid'])) {
            return $arr;
        }
        $arr['errmsg'] = $arr['errmsg'] ?? '未知';
        throw new \Exception("错误异常," . $arr['errmsg']);
    }

    //打开连接
    private function curl($url, $post = false, $toJson = true) {
        if (is_array($post) && $toJson) {
            $post = json_encode($post, JSON_UNESCAPED_UNICODE);
        }
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); //这个是重点。

        if ($post) {
            curl_setopt($ch, CURLOPT_POST, 1);
            // return $post;
            curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
        }
        $result = curl_exec($ch);
        curl_close($ch);
        return $result;
    }

}
