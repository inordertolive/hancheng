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

namespace app\operation\validate;

use think\Validate;

/**
 * 客服常用词验证器
 * @package app\operation\validate
 * @author 似水星辰 [2630481389@qq.com]
 */
class Servicewords extends Validate
{
    //定义规则
    protected $rule = [
        //'app_limitTime' => 'require|number'
    ];

    protected $message = [
        //'app_limitTime.require' => '',
        //'app_limitTime.number' => '',
    ];

    // 定义验证场景
    protected $scene = [
        //'title' => ['title']
    ];
}
