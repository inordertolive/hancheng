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
namespace addons\Jiguang;

use app\common\controller\Addons;

/**
 * 极光插件
 * @package Addons\Jiguang
 * @author 似水星辰 [ 2630481389@qq.com ]
 */
class Jiguang extends Addons
{
    /**
     * @var array 插件信息
     */
    public $info = [
        // 插件名[必填]
        'name'        => 'Jiguang',
        // 插件标题[必填]
        'title'       => '极光插件',
        // 插件唯一标识[必填],格式：插件名.开发者标识.addons
        'identifier'  => 'jiguang.zbphp.addons',
        // 插件图标[选填]
        'icon'        => 'fa fa-fw fa-commenting-o',
        // 插件描述[选填]
        'description' => '极光插件',
        // 插件作者[必填]
        'author'      => '似水星辰',
        // 作者主页[选填]
        'author_url'  => '',
        // 插件版本[必填],格式采用三段式：主版本号.次版本号.修订版本号
        'version'     => '1.0.0',
        // 是否有后台管理功能[选填]
        'admin'       => '0',
    ];

    /**
     * @var string 原数据库表前缀
     */
    public $database_prefix = 'lw_';

    /**
     * 安装方法
     * @return bool
     */
    public function install(){
        return true;
    }

    /**
     * 卸载方法必
     * @return bool
     */
    public function uninstall(){
        return true;
    }
}