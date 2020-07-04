<?php
// +----------------------------------------------------------------------
// | LwwanPHP[基于ThinkPHP5.1开发]
// +----------------------------------------------------------------------
// | Copyright (c) 2016-2019 http://www.lwwan.com
// +----------------------------------------------------------------------
// | Author 似水星辰 [ 2630481389@qq.com ]
// +----------------------------------------------------------------------
// | 星辰工作室 QQ群331378225
// +----------------------------------------------------------------------
namespace app\user\validate;


use think\Validate;

/**
 * 登录注册验证器
 * Class login
 * @package app\mall\validate
 */
class Login extends Validate
{
    //定义验证规则
    protected $rule = [
        'mobile' => 'mobile|unique:mall_user',
        'pwd|密码' => 'require',
        'code|验证码' => 'require',
        'verification|短信验证码' => 'require',
        'qq|qq' => 'require',
    ];
    //定义验证提示
    protected $message = [
        'mobile.unique' => '账号已存在',
        'pwd.require' => '密码不能为空',
        'code.require' => '验证码不能为空',
        'verification.require' => '短信验证码不能为空',
        'qq.require' => 'QQ不能为空',
    ];
    //定义验证场景
    protected $scene = [
        //登录
        'signin' => ['mobile' => 'require', 'pwd' => 'require'],
    ];
}