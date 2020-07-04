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

if (!function_exists('user_is_signin')) {
    /**
     * 判断是否登录
     * @author 似水星辰 [ 2630481389@qq.com ]
     * @return mixed
     */
    function user_is_signin()
    {
        $user = session('mall_user_auth');
        if (empty($user)) {
            return 0;
        }else{
            return session('mall_user_auth_sign') == data_auth_sign($user) ? $user['uid'] : 0;
        }
    }
}

if (!function_exists('user_role_auth')) {
    /**
     * 读取当前用户权限
     * @author 似水星辰 [ 2630481389@qq.com ]
     */
    function user_role_auth()
    {
        session('user_role_menu_auth', model('user/role')->roleAuth());
    }
}