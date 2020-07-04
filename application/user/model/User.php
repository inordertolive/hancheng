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
use think\helper\Hash;

/**
 * 单页模型
 * @package app\user\model
 */
class User extends ThinkModel
{
    // 设置当前模型对应的完整数据表名称
    protected $table = '__USER__';

    // 自动写入时间戳
    protected $autoWriteTimestamp = true;

    // 对密码进行加密
    public function setPasswordAttr($value)
    {
        return Hash::make((string)$value);
    }
    
    /**
     * 加密一个支付密码
     * @param string $pay_pass
     * @return type
     */
    public static function getPayPass($pay_pass)
    {
        return Hash::make('###' . $pay_pass);
    }

    /**
     * 检测一个支付密码
     * @param $pay_pass
     * @param $hash
     * @author 似水星辰 [ 2630481389@qq.com ]
     * @since 2019/4/7 13:48
     * @return mixed
     */
    public static function checkPayPass($pay_pass, $hash)
    {
        return Hash::check('###' . $pay_pass, $hash);
    }

    /**
     * 自动注册第三方登录,开放平台
     * @param string $openid 微信/qq的openid或者unionid
     * @param string $type 代理注册类型可选 qq,wx
     * @param array $ext 更多可选字段
     * @return bool|int|string
     */
    public static function autoRegOpenid($openid, $unionid, $type = 'wx', $ext = [])
    {
        $user_login = $type . '_' . uniqid();
        $default = [
            'user_nickname' => $user_login,
            'password' => '',
            'sex' => 0,
            'head_img' => '',
        ];
        $data = array_merge($default, $ext);
        $data['slat'] = mt_rand(1000, 9999);
        if ($data['password']) {
            $data['password'] = self::getPassword($data['password'], $data['slat']);
        }
        $data['user_login'] = $user_login;
        $data['user_type'] = 0;
        $data['create_time'] = time();
        $data['status'] = 1;
        if ($type == 'wx' && $unionid) {
            $data['binding_wechat'] = 1;
        }
        $data['token'] = self::getToken($openid . $unionid . $type);

        Db::startTrans();
        try {
            $user_id = self::insertGetId($data);
            if (!$user_id) {
                throw new \Exception('插入会员失败');
            }
            $saveData = ['user_id' => $user_id];
            if ($type == 'wx') {
                $saveData['open_openid'] = $openid;
                $saveData['wx_unionid'] = $unionid;
            } else {
                $saveData['qq_openid'] = $openid;
                $saveData['qq_unionid'] = $unionid;
            }
            $ret = \app\member\model\ThirdBind::autoCreate($saveData);
            if (!$ret) {
                throw new \Exception('插入绑定记录失败');
            }
            Db::commit();
            return $user_id;
        } catch (\Exception $e) {
            Db::rollback();
            return false;
        }
    }

    /**
     * 自动注册手机号
     * @param string $mobile 手机号
     * @param array $ext 更多可选字段
     * @return bool|int|string
     */
    public static function autoRegMobile($mobile, $ext = [])
    {
        $default = [
            'nickname' => '手机用户' . substr($mobile, -4),
            'password' => '',
            'sex' => 0,
            'avatar' => '',
        ];
        $data = array_merge($default, $ext);
        $data['slat'] = mt_rand(1000, 9999);
        if ($data['password']) {
            $data['password'] = self::getPassword($data['password'], $data['slat']);
        }
        $data['user_login'] = $mobile;
        $data['mobile'] = $mobile;
        $data['user_type'] = 0;
        $data['create_time'] = time();
        $data['status'] = 1;
        $data['token'] = self::getToken($mobile);
        try {
            return self::insertGetId($data);
        } catch (\Exception $e) {
            //echo $e->getMessage();
            \think\Log::write('error autoRegMobile : ', $e->getMessage(), 'info');
            return false;
        }
    }

    /**
     * 获取连接SOCKET句柄
     * @author 晓风<215628355@qq.com>
     * @param int $roomnum 房间ID 就是主播ID
     * @param string $stream 直播码,如是点播 可以传 void + 点播ID
     * @param array $user 会员MEMBER数据 可以为空，为空就是游客
     * @return boolean
     */
    public static function getSocketHandle($roomnum, $stream = '', $user = [])
    {
        //获取redis原始句柄
        $redis = \app\common\model\Redis::handler();
        $randUid = rand(1000, 9999);
        //创建句柄
        $token = get_order_sn('Z');

        $user_id = $user['id'] ? $user['id'] : '-' . $randUid;
        $nickname = $user['user_nickname'] ? $user['user_nickname'] : '游客' . $randUid;
        $userType = 0;
        if (isset($user['uType']) && null !== $user['uType']) {
            $userType = $user['uType'];
        } else if ($user_id > 0) {
            $userType = \app\lives\model\AdminUser::isAdmin($user_id, $roomnum);
        }
        if ($userType == 100) {
            $redis->hset("super", $user_id, 1);
        }
        $avatar = !empty($user['avatar']) ? get_file_url($user['avatar']) : '';

        $handler = [
            'uid' => $user_id,
            'roomnum' => $roomnum,
            'stream' => $stream,
            'token' => $token,
        ];
        //写入会员信息
        $unserInfo = [
            'id' => $user_id,
            'sign' => $token,
            'user_nickname' => $nickname,
            'userType' => $userType,
            'avatar' => $avatar,
        ];

        //将会员信息写入redis
        $unserInfo = json_encode($unserInfo);
        $redis->set($token, $unserInfo, 86400);
        //返回句柄
        return $handler;
    }

    /**
     * 检测用户状态
     * @param type $user
     * @return boolean|string
     */
    public static function checkStatus($user)
    {
        if ($user['status'] != 1 && $user['out_time'] == -1) {
            return '该用户已被永久封禁';
        }
        $time = $user['out_time'] - time();
        if ($user['status'] != 1 && $time > 0) {
            $time = timeToHIS($time, '时/分/秒');
            return '该用户已被封禁,还剩余' . $time;
        }
        return true;
    }

    /**
     * 绑定业务关系
     * @param int $id 会员ID
     * @param int $parent_id 推荐人ID
     * @param bool $is_force 是否强制绑定
     * @return int|bool
     * @throws \think\Exception
     * @throws \think\exception\PDOException
     */
    public static function bindParent($id, $parent_id, $is_force = false)
    {
        $map['id'] = $id;
        if (!$is_force) {
            $map['parent_id'] = ['eq', 0];
        }
        return self::where($map)->update(['parent_id' => $parent_id]);
    }

    /**
     * 获得向下层级关系
     * @param int $id 会员ID
     * @param int $num 获取层级关系的深度
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public static function getSonIds($id, $num = 3)
    {
        $ids = [];
        do {
            $num--;
            $user = self::where("parent_id", $id)->find();
            if (!$user) {
                break;
            }
            $id = $user['id'];
            $ids[] = $id;
            if ($num < 1)
                break;
        } while (true);
        return $ids;
    }

    /**
     * 获得向上层级关系
     * @param int $id 会员ID
     * @param int $num 获取层级关系的深度
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public static function getParentIds($id, $num = 3)
    {
        $ids = [];
        do {
            $num--;
            $user = self::where("id", $id)->find();
            if (!$user) {
                break;
            }
            $id = $user['parent_id'];
            if (!$id) {
                break;
            }
            $ids[] = $id;
            if ($num < 1)
                break;
        } while (true);
        return $ids;
    }


    /**
     * 使用手机号+验证码 自动登录+注册流程
     * @param $mobile
     * @param $code
     * @param $code_id
     * @param array $ext
     * @author 似水星辰 [ 2630481389@qq.com ]
     * @since 2019/4/7 13:52
     * @return mixed
     * @throws \Exception
     */
    public static function autoRegCodeLogin($mobile, $code, $code_id, $ext = [])
    {

        if (!$code_id) {
            throw new \Exception('验证码业务序号必填');
        }
        $logSmsModel = new LogSms();
        $result = $logSmsModel->verify_code($code, $mobile, '', $code_id);
        if (!$result) {
            throw new \Exception('登录失败,验证码已过期');
        }
        $user = self::get(['user_login' => $mobile]);
        if ($user) {
            $status = self::checkStatus($user);
            if (true !== $status) {
                throw new \Exception($status);
            }
            $uid = $user['id'];
        } else {
            //自动创建用户
            $password = rand(100000, 999999);

            $data = ['password' => $password];
            if (!empty($ext['nickname'])) {
                $data['nickname'] = $ext['nickname'];
            }
            if (isset($ext['sex'])) {
                $data['sex'] = $ext['sex'];
            }
            if (!empty($ext['avatar'])) {
                $data['avatar'] = $ext['avatar'];
            }

            $uid = self::autoRegMobile($mobile, $data);
            if (!$uid) {
                throw new \Exception('创建新用户失败');
            }
            $user = self::get($uid);
            //发送短信通知
            try {
                $result = plugin_action('DySms/DySms/send', [$mobile, ['name' => $mobile, 'code' => $password], '注册提醒']);
            } catch (\Exception $e) {
                \think\Log::write("send reg pass code error:", $e->getMessage(), 'error');
            }
        }
        if ($user['user_type'] == -1) {
            throw new \Exception('该账户不允许登录');
        }
        return $user;
    }

    /**
     * 登录回调事件，用于绑定业务关系等
     * @param $user
     * @param array $ext
     * @author 似水星辰 [ 2630481389@qq.com ]
     * @since 2019/4/7 13:53
     * @return void
     */
    public static function loginCallback($user, $ext = [])
    {

        try {
            //更新用户资料
            $data = [];
            if (!empty($ext['nickname']) && (0 === strpos($user['nickname'], '手机用户') || 0 === strpos($user['nickname'], 'wx_') || 0 === strpos($user['nickname'], 'qq_'))) {
                $data['nickname'] = $ext['nickname'];
            }
            if (isset($ext['sex']) && $user['sex']) {
                $data['sex'] = is_numeric($ext['sex']) ? $ext['sex'] : ($ext['sex'] == '男' ? 1 : 2);
            }
            if (!empty($ext['avatar']) && !$user['avatar']) {
                $data['avatar'] = $ext['avatar'];
            }
            if ($data) {
                self::where("id", $user['id'])->update($data);
            }

            //绑定业务关系
            if ($user['parent_id'] <= 0) {
                $parent_id = \app\member\model\TempParentUser::getParentId($user['mobile']);
                $parent_id && self::bindParent($user['id'], $parent_id) && \app\member\model\TempParentUser::delParentId($user['mobile']);
            }
        } catch (\Exception $e) {
            \think\Log::write("bind parent error:", $e->getMessage(), 'error');
        }
    }
}