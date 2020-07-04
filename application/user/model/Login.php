<?php
// +----------------------------------------------------------------------
// | LwwanPHP[基于ThinkPHP5.1开发]
// +----------------------------------------------------------------------
// | Copyright (c) 2016-2019 http://www.lwwan.com
// +----------------------------------------------------------------------
// | Author 似水星辰 [ 2630481389@qq.com ]
// +----------------------------------------------------------------------
// | 星辰工作室 QQ群331378225
// +----------------------------------------------------------------------
namespace app\user\model;

use think\helper\Hash;
use think\Model;

class Login extends Model
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
     * @param $mobile
     * @param $pwd
     * @return mixed
     * @throws \Exception
     */
    public function login($username, $password, $rememberme = false)
    {
        $username = trim($username);
        $password = trim($password);

            // 用户名登录
        $map['mobile'] = $username;
        $map['status'] = 1;
        // 查找用户
        $user = self::get($map);
        if (!$user) {
            $this->error = '用户不存在或被禁用';
            return false;
        }
        if (!Hash::check((string)$password, $user['password'])) {
            $this->error = '账号或者密码错误';
            return false;
        } else {
            $uid = $user['id'];
            // 更新登录信息
            $user['last_login_time'] = request()->time();
            $user['last_login_ip'] = get_client_ip(1);
            if ($user->save()) {
                return self::autoLogin(self::get($uid), $rememberme);
            } else {
                // 更新登录信息失败
                $this->error = '登录信息更新失败，请重新登录';
                return false;
            }
        }
    }

    /**
     * @param $user
     * @return mixed
     */
    public function autoLogin($user)
    {
        // 记录登录SESSION和COOKIES
        $auth = array(
            'uid'             => $user->id,
            'user_type'       => $user->user_type,
            'user_auth'       => $user->user_auth,
            'head_img'          => get_file_url($user->head_img),
            'user_name'       => $user->user_name,
            'user_nickname'   => $user->user_nickname,
            'mobile'          => $user->mobile,
            'last_login_time' => $user->last_login_time,
            'last_login_ip'   => get_client_ip(1),
        );
        session('mall_user_auth', $auth);
        session('mall_user_auth_sign', data_auth_sign($auth));

        return $user->id;
    }

    /**
     * 重置密码
     * @param $data
     * @throws \Exception
     */
    public function forgetPassword($data)
    {
        $password = $data['pwd'];
        $confirmPassword = $data['pwds'];
        $mobile = $data['mobile'];
        $verification = $data['verification'];   //短信验证
        $code = $data['verification'];           //验证码
        if ($password !== $confirmPassword) {
            throw new \Exception('两次密码输入不相同');
        }
        $account = self::get(array('mobile' => $mobile));
        if (empty($account)) {
            throw new \Exception('当前用户不存在');
        }
        if (!LogSms::verify_code($verification, $mobile, 'verify_forget_password')) {
            throw new \Exception('验证码错误或已过期');
        }
        $newPassword = Hash::make($password);
        $result = self::where(array('id' => $account['id']))->update(array('password' => $newPassword));
        if (!$result) {
            throw new \Exception('修改密码失败');
        }
    }

}