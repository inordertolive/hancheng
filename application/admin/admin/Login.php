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

namespace app\admin\admin;

use app\common\controller\Common;
use app\admin\model\Login as LoginModel;
use app\admin\model\Role as RoleModel;
use app\admin\model\Menu as MenuModel;

/**
 * 后台登录控制器，不经过权限认证
 * @package app\admin\login
 */
class Login extends Common
{
    /**
     * 用户登录
     * @author 似水星辰 [ 2630481389@qq.com ]
     * @return mixed
     */
    public function signin()
    {
        if ($this->request->isPost()) {
            // 获取post数据
            $data = $this->request->post();

            // 验证数据
            $result = $this->validate($data, 'Login.signin');
            if(true !== $result){
                // 验证失败 输出错误信息
                $this->error($result);
            }

            // 验证码
            if (config('captcha_signin')) {
                $captcha = $this->request->post('captcha', '');
                $captcha == '' && $this->error('请输入验证码');
                if(!captcha_check($captcha, 'admin', config('captcha'))){
                    //验证失败
                    $this->error('验证码错误或失效');
                };
            }

            // 登录
            $UserModel = new LoginModel;
            $uid = $UserModel->login($data['username'], $data['password']);
            if ($uid) {
                $this->goUrl();
            } else {
                $this->error($UserModel->getError());
            }
        } else {

            if (is_signin()) {
                $this->goUrl();
            } else {
                return $this->fetch();
            }
        }
    }

    /**
     * 跳转到第一个有权限访问的url
     * @author 似水星辰 [ 2630481389@qq.com ]
     * @return mixed|string
     */
    private function goUrl()
    {
        if (session('admin_auth.role') == 1) {
            if($this->request->isAjax()){
                $this->success('登录成功', url('admin/index/index'));
            }
            $this->redirect('admin/index/index');
        }

        $default_module = RoleModel::where('id', session('admin_auth.role'))->value('default_module');
        $menu = MenuModel::get($default_module);
        if (!$menu) {
            $this->error('当前角色未指定默认跳转模块！');
        }


        $menu_url = explode('/', $menu['url_value']);
        role_auth();
        $url = action('admin/menu/getSidebarMenu', ['module_id' => $default_module, 'module' => $menu['module'], 'controller' => $menu_url[1]], 'admin');
        if ($url == '') {
            $this->error('权限不足');
        } else {
            $this->success('登录成功', $url);
        }
    }

    /**
     * 退出登录
     * @author 似水星辰 [ 2630481389@qq.com ]
     */
    public function signout()
    {
        $hook_result = \Hook::listen('signout_sso');
        if (!empty($hook_result) && true !== $hook_result[0]) {
            if (isset($hook_result[0]['url'])) {
                $this->redirect($hook_result[0]['url']);
            }
            if (isset($hook_result[0]['error'])) {
                $this->error($hook_result[0]['error']);
            }
        }

        session(null);
        cookie('uid', null);
        cookie('signin_token', null);
        $this->redirect('signin');
    }
}
