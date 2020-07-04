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
 * 行为验证器
 * @package app\admin\validate
 * @author 似水星辰 [ 2630481389@qq.com ]
 */
class Action extends Validate
{
    //定义验证规则
    protected $rule = [
        'module|所属模块' => 'require|token',
        'name|行为标识'   => 'require|regex:^[a-zA-Z]\w{0,39}$|unique:admin_action,name^module',
        'title|行为名称'  => 'require|length:1,80',
        'remark|行为描述' => 'require|length:1,128'
    ];

    //定义验证提示
    protected $message = [
        'name.regex' => '行为标识由字母和下划线组成',
    ];
}
