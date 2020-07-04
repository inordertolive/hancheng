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
namespace app\user\model;

/**
 * 提现账户
 * Class WithdrawAccount
 * @package app\user\model
 * @author 似水星辰 [ 2630481389@qq.com ]
 * @created 2019/9/13 20:50
 */
class WithdrawAccount extends \think\Model{
    
    protected $table = '__USER_WITHDRAW_ACCOUNT__';
    
        
    // 自动写入时间戳
    protected $autoWriteTimestamp = true;
    
    
}
