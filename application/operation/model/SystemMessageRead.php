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
namespace app\operation\model;

use think\Model as ThinkModel;
/**
 * 站内信阅读状态
 * Class SystemMessageRead
 * @package app\user\model
 * @author 似水星辰 [ 2630481389@qq.com ]
 * @since 2019/4/9 16:23
 */
class SystemMessageRead extends ThinkModel  {
    
    //设置表名
     protected $table = '__OPERATION_SYSTEM_MESSAGE_READ__';
    // 自动写入时间戳
     protected $autoWriteTimestamp = true;
     
     public static function setread($user_id,$sys_msg_id){  
         return self::create(['sys_msg_id'=>$sys_msg_id,'user_id'=>$user_id,'status'=>1]); 
     }
     
     public static function getread($user_id,$sys_msg_id){         
         return self::get(['sys_msg_id'=>$sys_msg_id,'user_id'=>$user_id]); 
     }
     
     public static function delread($aid){         
         return self::where("aid",$aid)->update(['status'=>2]); 
     }
     
     public static function delMsg($sys_msg_id){
        return self::where("sys_msg_id",$sys_msg_id)->delete(); 
     }
}
