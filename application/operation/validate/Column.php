<?php

namespace app\operation\validate;

use think\Validate;

/**
 * 优惠券验证器
 * @package app\operation\validate
 * @author 似水星辰 [ 2630481389@qq.com ]
 */
class Column extends Validate
{
    //定义验证规则
    protected $rule = [
        'pid|所属栏目' => 'require',
        'name|栏目名称'  => 'require',
        'type|栏目属性' => 'require',
		'hide|是否隐藏栏目'   => 'require',
        'status|是否启用'  => 'require',

    ];

    //定义验证提示
  /*  protected $message = [
        'name.require' => '优惠券名称不能为空',
		'start_time.require' => '请选择开始发放时间',
		'end_time.require' => '请选择发放结束时间',
		'money.require' => '请填写面值，免运费类型填写0即可',
		'min_order_money.float' => '请填写有效数字',
		'min_order_money.require' => '请填写最低使用金额',
		'valid_day.require' => '请填写有效天数',
		'status.require' => '请选择状态',
    ];*/
}
