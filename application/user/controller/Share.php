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
namespace app\user\controller;

use think\Controller;

/**
 *分享控制器
 * @package app\User\controller
 */
class Share extends Controller
{
    /**
     * 分享页
     * @author 似水星辰 [2630481389@qq.com]
     * @return mixed
     * @throws \think\exception\DbException
     */
    public function index()
    {
        $invite_code = input('param.invite_code');
        $this->assign('invite_code', $invite_code);
        return $this->fetch();
    }
}