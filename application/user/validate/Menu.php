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

namespace app\user\validate;

use think\Validate;

/**
 * 会员菜单验证器
 * @package app\user\validate
 * @author 似水星辰 [2630481389@qq.com]
 */
class Menu extends Validate
{
    //定义规则
    protected $rule = [
        'title' => 'require'
    ];

    protected $message = [
        'title.require' => '请填写菜单名称',
        //'app_limitTime.number' => '',
    ];

    // 定义验证场景
    protected $scene = [
        'add' => ['title'],
		'edit' => ['title']
    ];
}
