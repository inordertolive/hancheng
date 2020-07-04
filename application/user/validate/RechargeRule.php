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
 * 充值规则验证器
 * @author 似水星辰 [ 2630481389@qq.com ]
 */
class RechargeRule extends Validate {

    protected $rule = [
        'name' => 'require',
        'money' => 'require',
        'status' => 'require|in:0,1',
    ];
    protected $message = [
        'name.require' => '规则名称不能为空',
        'money.require' => '充值金额不能为空',
        'status' => '状态必须为数字整数（0,1）',
        'status.require' => '状态不能为空',
    ];
    protected $scene = [
        'add' => ['name', 'money'],
        'edit' => ['name', 'money'],
    ];

}
