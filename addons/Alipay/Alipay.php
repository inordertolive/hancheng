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
namespace addons\Alipay;
use app\common\controller\Addons;

/**
 * 支付宝支付组件
 * Class Alipay
 * @package addons\Alipay
 * @author 似水星辰 [ 2630481389@qq.com ]
 * @created 2019/4/28 9:26
 */
class Alipay extends Addons
{
    /**
     * @var array 插件信息
     */
    public $info = [
        // 插件名[必填]
        'name'        => 'Alipay',
        // 插件标题[必填]
        'title'       => '支付宝支付组件',
        // 插件唯一标识[必填],格式：插件名.开发者标识.addons
        'identifier'  => 'Alipay.zbphp.addons',
        // 插件图标[选填]
        'icon'        => 'fa fa-fw fa-plug',
        // 插件描述[选填]
        'description' => '支持支付宝网关支付和APP支付，必须是同一商户下的,如需使用旧版接口,请参阅使用说明',
        // 插件作者[必填]
        'author'      => '似水星辰',
        // 作者主页[选填]
        'author_url'  => 'javascript:;',
        // 插件版本[必填],格式采用三段式：主版本号.次版本号.修订版本号
        'version'     => '1.1.0',
        // 是否有后台管理功能[选填]
        'admin'       => '0',
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
     * @author 似水星辰 [ 2630481389@qq.com ]
     * @return bool
     */
    public function install(){
        return true;
    }

    /**
     * 卸载方法必
     * @author 似水星辰 [ 2630481389@qq.com ]
     * @return bool
     */
    public function uninstall(){
        return true;
    }
}
