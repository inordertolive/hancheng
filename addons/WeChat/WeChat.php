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
namespace addons\WeChat;

use app\common\controller\Addons;

/**
 * 微信公众号授权，PC授权，公众号支付，开放平台支付 组件
 * @package addons\Wechat
 * @author 似水星辰 [ 2630481389@qq.com ]
 */
class WeChat extends Addons
{
    /**
     * @var array 插件信息
     */
    public $info = [
        // 插件名[必填]
        'name' => 'WeChat',
        // 插件标题[必填]
        'title' => '微信公众号+开放平台+PC组件',
        // 插件唯一标识[必填],格式：插件名.开发者标识.addons
        'identifier' => 'wechat.lbphp.addons',
        // 插件图标[选填]
        'icon' => 'fa fa-fw fa-plug',
        // 插件描述[选填]
        'description' => '支持微信公众号网页授权，PC扫码授权，公众号支付，公众号JSAPI支付，开放平台支付',
        // 插件作者[必填]
        'author' => '似水星辰',
        // 作者主页[选填]
        'author_url' => 'javascript:;',
        // 插件版本[必填],格式采用三段式：主版本号.次版本号.修订版本号
        'version' => '1.0.0',
        // 是否有后台管理功能[选填]
        'admin' => '0',
    ];

    /**
     * @var array 插件钩子
     */
    public $hooks = [];

    public static function run()
    {

    }

    /**
     * 安装方法
     * @return bool
     * @author 似水星辰 [ 2630481389@qq.com ]
     */
    public function install()
    {
        return true;
    }

    /**
     * 卸载方法必
     * @return bool
     * @author 似水星辰 [ 2630481389@qq.com ]
     */
    public function uninstall()
    {
        return true;
    }
}
