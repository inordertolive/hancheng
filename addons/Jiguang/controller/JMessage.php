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

namespace addons\Jiguang\controller;

require_once(dirname(dirname(__FILE__))."/sdk/JMessage/autoload.php");
use app\common\controller\Common;
use JMessage\JMessage as JM;
use JMessage\IM\User;
use JMessage\IM\Friend;
use JMessage\IM\Group;
use JMessage\IM\Admin;
use JMessage\IM\Resource;
use JMessage\IM\Message;
use JMessage\IM\Chatroom;
use JMessage\IM\SensitiveWord;
use JMessage\IM\Blacklist;
use think\facade\Config;

// 加载区域结点配置
Config::load();

/**
 * sms控制器
 * @package addons\DySms\controller
 * @author 小乌 <82950492@qq.com>
 */
class JMessage extends Common
{
    static $Client = null;

    /**
     * 取得Client
     * @param string $type  实例化的类型
     * @return DefaultAcsClient
     */
    public static function getClient($type = 'user') {
        // appKey
        $appKey = addons_config('Jiguang.appKey');

        // masterSecret
        $masterSecret = addons_config('Jiguang.masterSecret');

        if(static::$Client == null) {
            // 初始化AcsClient用于发起请求
            static::$Client = new JM($appKey, $masterSecret);
        }

        switch ($type){
            case 'user'://用户
                return new User(static::$Client);
                break;
            case 'friend'://好友
                return new Friend(static::$Client);
                break;
            case 'group'://分组
                return new Group(static::$Client);
                break;
            case 'admin'://管理员
                return new Admin(static::$Client);
                break;
            case 'resource'://资源（图片，视频，音频）
                return new Resource(static::$Client);
                break;
            case 'message'://消息
                return new Message(static::$Client);
                break;
            case 'chatroom'://房间
                return new Chatroom(static::$Client);
                break;
            case 'sensitiveWord'://敏感词
                return new SensitiveWord(static::$Client);
                break;
            case 'blacklist'://黑名单
                return new Blacklist(static::$Client);
                break;
            default:
                return '实例化类型错误';
        }
    }
}