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

/**
 * 获取广告位名称
 * @param $id 广告位id
 * @return void
 * @author 似水星辰 [ 2630481389@qq.com ]
 */
function get_ads_type($id){
    return Db::name('operation_ads_type')->where('id',$id)->value('name');
}

/**
 * 获取导航位名称
 * @param $id 导航位id
 * @return void
 * @author 似水星辰 [ 2630481389@qq.com ]
 */
function get_nav_type($id){
    return Db::name('operation_nav_type')->where('id',$id)->value('name');
}

if (!function_exists('operation_is_signin')) {
    /**
     * 判断是否登录
     * @author 似水星辰 [ 2630481389@qq.com ]
     * @return mixed
     */
    function operation_is_signin()
    {
        $user = session('operation_user_auth');
        if (empty($user)) {
            return 0;
        }else{
            return session('operation_user_auth_sign') == data_auth_sign($user) ? $user['uid'] : 0;
        }
    }
}