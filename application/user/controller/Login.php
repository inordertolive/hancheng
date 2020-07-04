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
namespace app\user\controller;

use app\common\controller\Common;
use app\user\model\Login as LoginModel;
use app\user\model\Menu as MenuModel;
use app\user\model\Role as RoleModel;
/**
 * 登录/注册
 * Class Login
 * @package app\mall\admin
 */
class Login extends Common
{
    /**
     * 会员登录
     * @return string
     */
    public function signin()
    {
        if ($this->request->isPost()) {
            // 获取post数据
            $data = $this->request->post();
            // 验证数据
            $result = $this->validate($data, 'Login.signin');
            if (true !== $result) {
                // 验证失败 输出错误信息
                return responseJson(0, [], $result);
            }
            // 登录
            $UserModel = new LoginModel;
            $uid = $UserModel->login($data['username'], $data['password']);
            if ($uid && $this->request->isAjax()) {
                // 读取默认菜单
                $default_menu = RoleModel::where('id', session('mall_user_auth.user_auth'))->value('default_menu');
                $menu = MenuModel::get($default_menu);
                if (!$menu) {
                    $this->error('当前角色未指定默认跳转模块！');
                }

                $menu_url = explode('/', $menu['url_value']);

                user_role_auth();
                $url = action('user/menu/getSidebarMenu', ['id' => $default_menu, 'module' => $menu['module'], 'controller' => $menu_url[1]]);
                if ($url == '') {
					session('mall_user_auth', null);
					session('mall_user_auth_sign', null);
                    $this->error('权限不足');
                }
                $this->success('登录成功', cookie('__forward__') ? cookie('__forward__') : $url);
            }else{
                $this->error($UserModel->getError());
            }
        } else {
            if (user_is_signin()) {
                $this->redirect('index/index');
            } else {
                return $this->fetch();
            }
        }
    }

    /**
     * 退出登录
     * @author 似水星辰 [2630481389@qq.com]
     */
    public function signout()
    {
        session('mall_user_auth', null);
        session('mall_user_auth_sign', null);
        $this->redirect('signin');
    }

    /**
     * 重置密码
     * @return mixed|string
     */
    public function forget_password()
    {
        if ($this->request->isPost()) {
            $data = $this->request->post();
            try {
                $UserModel = new LoginModel;
                $UserModel->forgetPassword($data);
                $this->success('修改成功，请重新登录', url('manage/publics/signin'));
            } catch (\Exception $e) {
                return responseJson(0, [], $e->getMessage());
            }
        } else {
            return $this->fetch();
        }
    }
}