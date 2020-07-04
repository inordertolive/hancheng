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
namespace addons\DySms\model;

use think\Model;

class LogSms extends Model {

    protected $table = '__ADDONS_DYSMS_LOG__';

    //设置大鱼短信的每日限制次数
    const DYMAXCOUNT = 10;

    /**
     * 获取指定手机号今日发送的次数
     * @author 晓风<215628355@qq.com>
     * @date 2018年11月14日
     * @param string $phone 手机号
     * @return int
     */
    public function getMobileTodayCount($phone) {
        $time1 = strtotime(date("Y-m-d"));
        $time2 = $time1 + 86400;
        $where = [
            'phone' => $phone,
            'add_time' => ['between', $time1, $time2]
        ];
        return $this->where($where)->count();
    }

    /**
     * 使用大鱼插件发送验证码
     * ！在调用时必须使用事务
     * @author 晓风<215628355@qq.com>
     * @date 2018年11月14日
     * @param string $phoneVerify  验证码
     * @param string $mobile  手机号
     * @param string $scene  场景
     * @param string $content 用户收到的内容
     * @param int $expiration  过期时间(分钟)
     * @return int  业务ID
     * @throws \Exception 
     */
    public function sendDySms($phoneVerify, $mobile, $scene = 'validate', $content = '', $expiration = 10) {
        //查询该手机号今日是否超出限额        
        $count = $this->getMobileTodayCount($mobile);
        if ($count > LogSms::DYMAXCOUNT) {
            throw new \Exception('该手机号今日发送次数已超出限额，请勿频繁发送');
        }
        //先写表，这样在事物回滚时会取消写入操作，避免写入失败造成发送出短信，形成经济损失
        $code_id = $this->add_sms($phoneVerify, $mobile, $scene, $content, $expiration);
        if (!$code_id) {
            throw new \Exception('发送失败');
        }
        //最后一步发送短信，如果先发短信，若数据库写入失败则不可逆转
        $result = plugin_action('DySms/DySms/send', [$mobile, ['code' => $phoneVerify], '验证码']);
        if ($result['code']) {
            throw new \Exception('发送失败，错误代码：' . $result['code'] . ' 错误信息：' . $result['msg']);
        }
        return $code_id;
    }

    /**
     * 插入短信验证码
     * @param $code 验证码
     * @param $phone 手机号
     * @param $type 验证类型
     * @param $content 内容
     * @param int $expiration 过期时间
     * @return false|int
     * @author 似水星辰 [ 2630481389@qq.com ]
     * @since 2019/4/7 16:37
     */
    public function add_sms($code, $phone, $type, $content = '', $expiration = 30) {
        if (empty($content)) {
            $content = '您的短信验证码为：' . $code . ',有效期为' . $expiration . '分钟';
        }
        $data = array(
            'code' => $code,
            'phone' => $phone,
            'type' => $type,
            'add_time' => time(),
            'ip' => get_client_ip(),
            'content' => $content,
            'expiration_time' => time() + $expiration * 60
        );
        //返回业务序号，方便后续接口验证验证码
        return $this->insertGetId($data);
    }


    /**
     * 判断验证码正确性
     * @param $code 验证码
     * @param $phone 手机号
     * @param string $type 验证类型
     * @param int $code_id 验证码表id
     * @return array|\PDOStatement|string|Model|null
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @throws \think\exception\PDOException
     * @author 似水星辰 [ 2630481389@qq.com ]
     * @since 2019/4/7 16:37
     */
    public static function verify_code($code, $phone, $type , $code_id = 0) {
        $where = [
            'code' => $code,
            'phone' => $phone,
            'status' => 0
        ];
        if ($type) {
            $where['type'] = $type;
        }
        if ($code_id > 0) {
            $where['aid'] = $code_id;
        }
        $time = time();
        $data = self::where($where)->where('expiration_time','EGT', $time)->order('aid desc')->find();
        if ($data){
            //更改验证码状态
            $res = self::where($where)->update(['status'=>1]);
            if ($res) {
                return $data;
            }
        }
        return false;
    }

}
