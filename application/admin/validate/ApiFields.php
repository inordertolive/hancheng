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
 * 接口字段验证器
 * @package app\admin\validate
 * @author 似水星辰 [ 2630481389@qq.com ]
 */
class ApiFields extends Validate
{
    protected $rule = [
        //'fieldName' => 'require|alphaDash',
    ];

    protected $message = [
        'fieldName.require' => '字段名称不能为空',
        //'fieldName.alphaDash' => '字段名称只能是字母和数字，下划线_及破折号-',
    ];

    protected $scene = [
        'add'   => ['fieldName'],
        'edit'  => ['fieldName'],
        'fieldName' => ['fieldName'],
        'default' => ['default'],
        'info' => ['info'],
    ];
}