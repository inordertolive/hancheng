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
 * 会员认证验证器
 * @package app\user\validate
 * @author 似水星辰 [2630481389@qq.com]
 */
class Certified extends Validate
{
    //定义规则
    protected $rule = [
        'name' => 'require',
		'idcard_no' => 'require|idCard',
		'idcard_front' => 'require',
		'idcard_reverse' => 'require'
    ];

    protected $message = [
        'name.require' => '请填写姓名',
        'idcard_no.require' => '请填写身份证号码',
        'idcard_no.idCard' => '请填写正确的身份证号码',
		'idcard_front.require' => '请上传身份证正面照',
        'idcard_reverse.require' => '请上传身份证反面照',
    ];

    // 定义验证场景
    protected $scene = [
        'add' => ['name','idcard_no','idcard_front','idcard_reverse']
    ];
}
