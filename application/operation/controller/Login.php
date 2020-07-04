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

namespace app\operation\controller;

use app\common\controller\Common;
use app\operation\model\Service;

/**
 * 用户公开控制器，不经过权限认证
 * @package app\user\admin
 */
class Login extends Common
{
    /**
     * 用户登录
     * @author 似水星辰 [2630481389@qq.com]
     * @return mixed
     */

    public function signin()
    {
        if ($this->request->isPost()) {

            // 获取post数据
            $data = $this->request->post();
            $rememberme = isset($data['remember-me']) ? true : false;

            // 验证数据
            $result = $this->validate($data, 'Account.signin');
            if(true !== $result){
                // 验证失败 输出错误信息
                $this->error($result);
            }

            // 验证码
            if (config('captcha_signin')) {
                $captcha = $this->request->post('captcha', '');
                $captcha == '' && $this->error('请输入验证码');
                if(!captcha_check($captcha, '', config('captcha'))){
                    //验证失败
                    $this->error('验证码错误或失效');
                };
            }

            // 登录
            $UserModel = new Service;
            $uid = $UserModel->login($data['username'], $data['password']);
            if ($uid) {
                if($this->request->isAjax()){
                    $this->success('登录成功', url('operation/index/index'));
                }
                $this->redirect('operation/service/index');
            } else {
                $this->error($UserModel->getError());
            }
        } else {

            if (operation_is_signin()) {
                $this->jumpUrl();
            } else {
                return $this->fetch();
            }
        }
    }

    /**
     * 跳转到第一个有权限访问的url
     * @author 似水星辰 [2630481389@qq.com]
     * @return mixed|string
     */
    private function jumpUrl()
    {
        if (session('operation_user_auth')) {
            $this->success('登录成功', url('operation/index/index'));
        }
    }

    /**
     * 退出登录
     * @author 似水星辰 [2630481389@qq.com]
     */
    public function signout()
    {
        session('operation_user_auth',null);
        session('operation_user_auth_sign',null);
        $this->redirect('signin');
    }

    /**忘记密码
     * @return mixed
     */
    public function forget_password(){
        if($this->request->isPost()){
            $param = $this->request->post();
            $password = $param['password'];
            $phone = $param['phone'];
            $code = $param['verify_code'];
            $confirmPassword = $param['confirm_password'];
            if($password !== $confirmPassword){
                $this->error('两次密码输入不相同');
            }
            $account =  Account::get(array('mobile'=>$phone));
            if(!$account){
                $this->error('当前用户不存在');
            }
            if (!LogSms::verify_code($code, $phone, 'verify_forget_password')) {
                $this->error('验证码错误或已过期');
            }
            $newPassword = Hash::make($password);
            $result = Account::where(array('id' => $account['id']))->update(array('password' => $newPassword));
            if (!$result) {
                $this->error('修改密码失败');
            }
            $this->success('修改成功，请重新登录', url('manage/publics/signin'));
        }else{
            return $this->fetch();
        }
    }
}
