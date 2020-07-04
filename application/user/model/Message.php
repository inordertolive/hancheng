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
 * 私信
 * Class Message
 * @package app\member\model
 * @author 似水星辰 [ 2630481389@qq.com ]
 * @since 2019/4/9 16:14
 */
class Message extends ThinkModel{
    protected  $table = "__USER_MESSAGE__";
    
       // 自动写入时间戳
    protected $autoWriteTimestamp = true;
    
}
