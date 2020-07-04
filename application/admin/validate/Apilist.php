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
 * 接口验证器
 * @package app\admin\validate
 * @author 似水星辰 [ 2630481389@qq.com ]
 */
class Apilist extends Validate
{
    protected $rule = [
        'apiName' => 'require',
        'status' => 'require|in:0,1',
    ];

    protected $message = [
        'apiName.require' => '接口名称不能为空',
        'status' => '状态必须为数字整数（0,1）',
        'status.require' => '状态不能为空',
    ];

    protected $scene = [
        'add'   => ['apiName'],
        'edit'  => ['apiName'],
        'apiName' => ['apiName'],
        'status' => ['status'],
        'info' => ['info'],
    ];
}