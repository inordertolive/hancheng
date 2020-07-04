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

use app\admin\model\Login;
use think\Db;

class Index extends Base
{
    /**
     * 后台首页
     * @return mixed
     * @author 似水星辰 [ 2630481389@qq.com ]
     */
    public function index()
    {
        return $this->fetch();
    }

	/**
     * 清空系统缓存
     * @author 似水星辰 [ 2630481389@qq.com ]
     */
    public function Clear_Cache()
    {
        $this->clearCache();
        $this->success('清空缓存成功');
    }

    /**
     * 个人设置
     * @author 似水星辰 [ 2630481389@qq.com ]
     */
    public function Setting(){
        // 保存数据
        if ($this->request->isPost()) {
            $data = $this->request->post();

            $data['nickname'] == '' && $this->error('昵称不能为空');
            $data['id'] = UID;

            // 如果没有填写密码，则不更新密码
            if ($data['password'] == '') {
                unset($data['password']);
            }

            $UserModel = new Login();
            if ($user = $UserModel->allowField(['nickname', 'email', 'password', 'mobile', 'avatar'])->update($data)) {
                $info = $UserModel->where('id', UID)->field('password', true)->find();
                //刷新信息
                $UserModel->autoLogin($info);
                $this->success('编辑成功');
            } else {
                $this->error('编辑失败');
            }
        }

        // 获取数据
        $info = Login::where('id', UID)->field('password', true)->find();
        $fields =[
            ['type'=>'static', 'name'=>'username', 'title'=>'用户名', 'tips'=>'不可更改'],
            ['type'=>'text', 'name'=>'nickname', 'title'=>'昵称', 'tips'=>'可以是中文'],
            ['type'=>'text', 'name'=>'email', 'title'=>'邮箱'],
            ['type'=>'password', 'name'=>'password', 'title'=>'密码', 'tips'=>'6-20位,如不修改请保持为空','attr'=>'data-rule="length(6~20)"'],
            ['type'=>'text', 'name'=>'mobile', 'title'=>'手机号','attr'=>'data-rule="mobile" data-rule-mobile: "[/^1[3-9]\d{9}$/, "请填写正确的手机号"]"'],
            ['type'=>'image', 'name'=>'avatar', 'title'=>'头像']
        ];

        $this->assign('page_title','编辑配置');
        $this->assign('form_items', $this->setData($fields, $info));
        return $this->fetch('public/edit');
    }

    /**
     * 获取联动数据
     * @param string $token token
     * @param int $pid 父级ID
     * @param string $pidkey 父级id字段名
     * @author 似水星辰 [2630481389@qq.com]
     * @return \think\response\Json
     */
    public function getLevelData($token = '', $pid = 0, $pidkey = 'pid')
    {
        if ($token == '') {
            return json(['code' => 0, 'msg' => '缺少Token']);
        }

        $token_data = session($token);
        $table      = $token_data['table'];
        $option     = $token_data['option'];
        $key        = $token_data['key'];

        $data_list = Db::name($table)->where($pidkey, $pid)->column($option, $key);

        if ($data_list === false) {
            return json(['code' => 0, 'msg' => '查询失败']);
        }

        if ($data_list) {
            $result = [
                'code' => 1,
                'msg'  => '请求成功',
                'list' => format_linkage($data_list)
            ];
            return json($result);
        } else {
            return json(['code' => 0, 'msg' => '查询不到数据']);
        }
    }

}
