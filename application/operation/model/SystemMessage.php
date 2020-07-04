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
 * 站内信
 * Class SystemMessage
 * @package app\user\model
 * @author 似水星辰 [ 2630481389@qq.com ]
 * @since 2019/4/9 16:24
 */
class SystemMessage extends ThinkModel
{

    //设置表名
    protected $table = '__OPERATION_SYSTEM_MESSAGE__';
    // 自动写入时间戳
    protected $autoWriteTimestamp = true;

    public static $msgtype = [
        1 => '系统通知',
        2 => '活动提醒',
        3 => '会员消息',
        4 => '消费通知',
    ];
    //

    /**
     * 发送一个站内信
     * @param int $to_user_id 收件人ID 0是群发
     * @param string $content 信件内容
     * @param string $event 点击事件
     * @return boolean|string
     * @author 晓风<215628355@qq.com>
     */
    public function sendMsg($data)
    {
        switch ($data['type']){
            case 1:
                //单推
                $data['client_id'] = db('user')->where('id',$data['to_user_id'])->value('client_id');
                if(!$data['client_id']){
                    $this->error = '获取指定用户的client_id失败，无法发送';
                    return false;
                }
                $res = addons_action('Getui/Getui/pushMessageToSingle', [$data]);
                break;
            case 2:
                $data['client_id'] = db('user')->where('id','in',$data['to_user_id'])->column('client_id');
                if(!$data['client_id']){
                    $this->error = '获取指定用户的client_id失败，无法发送';
                    return false;
                }
                //多推
                $res = addons_action('Getui/Getui/pushMessageToList', [$data]);
                break;
            case 3:
                //群推
                $res = addons_action('Getui/Getui/pushMessageToApp', [$data]);
                break;
        }

        if($res['result'] == 'ok'){
            return true;
        }
        $this->error = $res['result'];
        return false;
    }


    /**
     * 获取我的系统消息
     * @param int $user_id 会员ID
     * @return object
     * @author 晓风<215628355@qq.com>
     */
    public static function getList($user_id,$msgtype = 0){
        $map = [];
        if($msgtype >0){
            $map['operation_system_message.msg_type'] = $msgtype;
        }
        return self::view("operation_system_message", 'to_user_id,title,content,id,is_read,msg_type,create_time')
            ->view("operation_system_message_read","aid as readid",'operation_system_message_read.sys_msg_id = operation_system_message.id and operation_system_message_read.user_id=:user_id',"left")
            ->bind(['user_id'=>$user_id])
            ->where(function($query)use($user_id){
                $query->where("operation_system_message.to_user_id",$user_id);
            })
            ->where($map)
            ->where("operation_system_message_read.aid IS NULL OR operation_system_message_read.status = 1")
            ->order("operation_system_message.create_time desc")
            ->paginate()->each(function($item){
                if(!$item['readid']){
                    SystemMessageRead::setread($item['to_user_id'],$item['id']);
                }
                $item['is_read'] = $item['readid'] ? 1 : 0;
            });

    }

    /**
     * 获取我的系统消息
     * @param int $user_id 会员ID
     * @return object
     * @author 晓风<215628355@qq.com>
     */
    public static function getNew($user_id, $msgtype = 0)
    {
        $map = [];
        if ($msgtype > 0) {
            $map['operation_system_message.msg_type'] = $msgtype;
        }
        return self::view("operation_system_message", true)
            ->view("operation_system_message_read", "aid as readid", 'operation_system_message_read.sys_msg_id = operation_system_message.id and operation_system_message_read.user_id=:user_id', "left")
            ->bind(['user_id' => $user_id])
            ->where(function ($query) use ($user_id) {
                $query->where("operation_system_message.to_user_id", $user_id);
            })
            ->where($map)
            ->where("operation_system_message_read.aid IS NULL OR operation_system_message_read.status = 1")
            ->order("operation_system_message.create_time desc")
            ->find();

    }
}
