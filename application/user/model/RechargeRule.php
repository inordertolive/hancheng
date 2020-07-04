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
use think\Model as ThinkModel;

/**
 * 充值规则管理
 * Class RechargeRule
 * @package app\user\model
 * @author 似水星辰 [ 2630481389@qq.com ]
 * @created 2019/5/8 16:08
 */
class RechargeRule extends ThinkModel{
    
    protected $table = '__USER_RECHARGE_RULE__';
    
    // 自动写入时间戳
    protected $autoWriteTimestamp = true;

    /**
     * 获取列表
     * @author 似水星辰 [ 2630481389@qq.com ]
     * @param array $map
     * @param array $order
     * @return object
     * @throws \think\exception\DbException
     */
    
    public static function getList($map=[],$order=[],$group = 0){
        
        return self::where($map)->where('group',$group)->order($order)->paginate();
    }
    
}
