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

/**
 * Class AuthCode 公众号授权相关基类
 * @package addons\WeChat\controller
 * @author 似水星辰 [ 2630481389@qq.com ]
 * @created 2019/9/9 22:09
 */
class AuthCode extends Base {

    public function __construct(\think\Request $request = null) {
        parent::__construct($request);
        $this->setConfig('code');
    }

    //返回实例本身
    function init($config = null) {
        $this->setConfig('code', $config);
        return $this;
    }

    //获取全局access_token
    public function getAccessToken() {
        $access_token = \think\Cache::get('wxcode_access_token');
        if (!$access_token) {
            $url = "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&";
            $arr = array(
                'appid' => \WeixinConfig::getConfig('APPID'),
                'secret' => \WeixinConfig::getConfig('APPSECRET'),
            );
            $url .= http_build_query($arr);
            $json = $this->curl($url);
            $arr = json_decode($json, true);
            $access_token = $arr['access_token'] ?? null;
            $expires_in = $arr['expires_in'] ?? 0;
            if ($access_token && $expires_in) {
                \think\Cache::set('wxcode_access_token', $access_token, $expires_in - 1800);
            } else {
                $arr['errmsg'] = $arr['errmsg'] ?? '未知';
                throw new \Exception("错误异常," . $arr['errmsg']);
            }
        }
        return $access_token;
    }

    //获取授权连接,返回一个CODE
    //https://mp.weixin.qq.com/wiki?t=resource/res_main&id=mp1455784140&token=&lang=zh_CN
    public function getAuthUrl($redirect_uri, $scope = 'snsapi_base', $state = 1) {

        $url = 'https://open.weixin.qq.com/connect/oauth2/authorize?';
        $arr = array(
            'appid' => \WeixinConfig::getConfig('APPID'),
            'redirect_uri' => $redirect_uri,
            'response_type' => 'code',
            'scope' => $scope,
            'state' => $state
        );
        return $url . http_build_query($arr) . '#wechat_redirect';
    }

    //微信授权回调,获取用户access_token 和 OPENID, unionid，用getAuthUrl回调的CODE
    public function getOpenid($code) {
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

    //获取JSAPI授权凭证
    // https://api.weixin.qq.com/cgi-bin/ticket/getticket?access_token=ACCESS_TOKEN&type=jsapi
    //获取全局access_token
    public function getJsapiTicket($access_token = '') {
        if (!$access_token) {
            $access_token = $this->getAccessToken();
        }
        $jsapi_ticket = \think\Cache::get('wxcode_jsapi_ticket');
        if (!$jsapi_ticket) {
            $url = "https://api.weixin.qq.com/cgi-bin/ticket/getticket?access_token=" . $access_token . "&type=jsapi";
            $json = $this->curl($url);
            $arr = json_decode($json, true);
            $jsapi_ticket = $arr['ticket'] ?? null;
            $expires_in = $arr['expires_in'] ?? 0;
            if ($jsapi_ticket && $expires_in) {
                \think\Cache::set('wxcode_jsapi_ticket', $jsapi_ticket, $expires_in - 300);
            } else {
                $arr['errmsg'] = $arr['errmsg'] ?? '未知';
                throw new \Exception("错误异常," . $arr['errmsg']);
            }
        }
        return $jsapi_ticket;
    }

    function is_https() {
        if (!empty($_SERVER['HTTPS']) && strtolower($_SERVER['HTTPS']) !== 'off') {
            return true;
        } elseif (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https') {
            return true;
        } elseif (!empty($_SERVER['HTTP_FRONT_END_HTTPS']) && strtolower($_SERVER['HTTP_FRONT_END_HTTPS']) !== 'off') {
            return true;
        }
        return false;
    }

    //获取JSAPI的config
    public function getJsapiConfig($selfurl = '', $nonce_str = '') {
        $ticket = $this->getJsapiTicket();
        if (!$ticket)
            return null;
        if (!$selfurl) {
            $http = $this->is_https() ? "https://" : "http://";
            $selfurl = $http . $_SERVER["SERVER_NAME"] . $_SERVER["REQUEST_URI"];
            $selfurl = explode('#', $selfurl);
            $selfurl = $selfurl[0];
        }
        if (!$nonce_str) {
            $nonce_str = $this->getNonceStr();
        }
        $url = array(
            'noncestr' => $nonce_str,
            'jsapi_ticket' => $ticket,
            'timestamp' => time(),
            'url' => $selfurl,
        );
        //签名算法        
        ksort($url); //字典排序
        $buff = "";
        foreach ($url as $k => $v) {
            if ($k != "sign" && $v != "" && !is_array($v)) {
                $buff .= $k . "=" . $v . "&";
            }
        }
        $buff = trim($buff, "&");
        $url['signature'] = sha1($buff);
        $url['appid'] = \WeixinConfig::getConfig('APPID');
        return $url;
    }

    //创建随机字符串
    public function getNonceStr($length = 8) {
        // 字符集，可任意添加你需要的字符    
        $chars = 'abcdefghijklmnopqrstuvwxyz0123456789';
        return substr(str_shuffle($chars), 0, $length);
    }

    //打开连接
    function curl($url, $post = false, $toJson = true) {
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
