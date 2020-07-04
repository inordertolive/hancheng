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

namespace app\admin\validate;

use think\Validate;

/**
 * 应用验证器
 * @package app\admin\validate
 * @author 似水星辰 [ 2630481389@qq.com ]
 */
class Apiapp extends Validate
{
    protected $rule = [
        'app_name' => 'require',
        'app_status' => 'require|in:0,1',
        'app_limitTime' => 'require|number'
    ];

    protected $message = [
        'app_name' => '应用名称不能为空',
        'app_status' => '状态必须为数字整数（0,1）',
        'app_status.require' => '状态不能为空',
        'app_limitTime.require' => 'Token有效时间不能为空',
        'app_limitTime.number' => 'Token有效时间必须是数字',
    ];

    protected $scene = [
        'add'   => ['app_name','app_limitTime'],
        'edit'  => ['app_name','app_limitTime'],
        'app_name' => ['app_name'],
        'app_status' => ['app_status'],
        'app_limitTime' => ['app_limitTime'],
    ];
}