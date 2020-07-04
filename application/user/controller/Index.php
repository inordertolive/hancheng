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

use app\user\model\Login;
/**
 * 后台默认控制器
 * @package app\admin\controller
 */
class Index extends Admin
{
    /**
     * 后台首页
     * @author 似水星辰 <2630481389@qq.com>
     * @return string
     */
    public function index()
    {
        return $this->fetch();
    }

    /**
     * 个人设置
     * @author 似水星辰 [ 2630481389@qq.com ]
     */
    public function Setting(){
        // 保存数据
        if ($this->request->isPost()) {
            $data = $this->request->post();

            $data['user_nickname'] == '' && $this->error('昵称不能为空');
            $data['id'] = UID;

            // 如果没有填写密码，则不更新密码
            if ($data['password'] == '') {
                unset($data['password']);
            }

            $UserModel = new Login();
            if ($user = $UserModel->allowField(['user_nickname', 'email', 'password', 'mobile', 'head_img'])->update($data)) {
                $info = $UserModel->where('id', UID)->field('password', true)->find();
                //刷新信息
                $UserModel->autoLogin($info);
                $this->success('编辑成功');
            } else {
                $this->error('编辑失败');
            }
        }

        // 获取数据
        $info = Login::where('id', USER_ID)->field('password', true)->find();
        $fields =[
            ['type'=>'text', 'name'=>'mobile', 'title'=>'手机号'],
            ['type'=>'static', 'name'=>'user_name', 'title'=>'真实姓名', 'tips'=>'不可更改'],
            ['type'=>'text', 'name'=>'user_nickname', 'title'=>'昵称', 'tips'=>'可以是中文'],
            ['type'=>'password', 'name'=>'password', 'title'=>'密码', 'tips'=>'6-20位,如不修改请保持为空'],
            ['type'=>'image', 'name'=>'head_img', 'title'=>'头像']
        ];

        $this->assign('page_title','编辑配置');
        $this->assign('form_items', $this->setData($fields, $info));
        return $this->fetch('public/edit');
    }
}