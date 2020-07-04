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
namespace addons\DySms;

use app\common\controller\Addons;

/**
 * 阿里云短信插件
 * @package Addons\DySms
 * @author 似水星辰 [ 2630481389@qq.com ]
 */
class DySms extends Addons
{
    /**
     * @var array 插件信息
     */
    public $info = [
        // 插件名[必填]
        'name'        => 'DySms',
        // 插件标题[必填]
        'title'       => '阿里云短信插件',
        // 插件唯一标识[必填],格式：插件名.开发者标识.addons
        'identifier'  => 'dy_sms.zbphp.addons',
        // 插件图标[选填]
        'icon'        => 'fa fa-fw fa-envelope-o',
        // 插件描述[选填]
        'description' => '阿里云短信插件',
        // 插件作者[必填]
        'author'      => '似水星辰',
        // 作者主页[选填]
        'author_url'  => '',
        // 插件版本[必填],格式采用三段式：主版本号.次版本号.修订版本号
        'version'     => '1.0.0',
        // 是否有后台管理功能[选填]
        'admin'       => '1',
    ];

    /**
     * @var array 管理界面字段信息
     */
    public $admin = [
        'title'        => '短信模板列表', // 后台管理标题
        'table_name'   => 'addons_dysms', // 数据库表名，如果没有用到数据库，则留空

        // 后台列表字段
        'columns' => [
            ['title', '模板名称'],
            ['code', '模板ID'],
		
            ['sign_name', '短信签名'],
            ['status', '状态', 'status'],
            ['right_button', '操作', 'btn'],
        ],

        // 右侧按钮
        'right_buttons' => [
            ['ident'=> 'edit', 'title'=>'编辑','href'=>['edit', ['id'=>'__id__', 'name' => 'DySms']],'icon'=>'fa fa-pencil pr5','class'=>'btn btn-xs mr5 btn-success btn-flat'],
			['ident'=> 'delete', 'title'=>'删除','href'=>['delete',['ids'=>'__id__', 'name' => 'DySms', 'table'=>'addons_dysms']], 'icon'=>'fa fa-times pr5','class'=>'btn btn-xs mr5 btn-danger btn-flat ajax-get confirm'],
        ],

        // 顶部栏按钮
        'top_buttons' => [
            'add',    // 使用系统自带的添加按钮
            'enable', // 使用系统自带的启用按钮
            'disable',// 使用系统自带的禁用按钮
            'delete', // 使用系统自带的删除按钮
        ],
    ];

    /**
     * @var array 新增或编辑的字段
     */
    public $fields = [
		['type'	=> 'hidden', 'name' => 'id'],
        ['type' => 'text', 'name' => 'title', 'title' => '模板名称', 'tips' => '必填，自定义填写，用于区分用途，比如：注册验证、密码修改'],
        ['type' => 'text', 'name' => 'code', 'title' => '模板ID', '必填'],
		['type' => 'textarea', 'name' => 'content', 'title' => '模板详情', 'tips' => '选填，复制的你的模板内容，以便开发时可以校对'],
        ['type' => 'text', 'name' => 'sign_name', 'title' => '短信签名', 'tips' => '在阿里云后台设置的短信签名'],
        ['type' => 'radio', 'name' => 'status', 'title' => '立即启用', 'extra' => ['1' => '是', '0' => '否'], 'value' => 1],
    ];

    /**
     * @var string 原数据库表前缀
     */
    public $database_prefix = 'lb_';

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