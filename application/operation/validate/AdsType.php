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
 * 广告分类验证器
 * @package app\cms\validate
 * @author 似水星辰 [2630481389@qq.com]
 */
class AdsType extends Validate
{
    // 定义验证规则
    protected $rule = [
        'name|广告位名称'  => 'require|length:1,30|unique:operation_ads_type'
    ];

    // 定义验证场景
    protected $scene = [
        'name' => ['name']
    ];
}
