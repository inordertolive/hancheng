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
namespace app\user\admin;

use app\admin\admin\Base;
use app\user\model\User as UserModel;
use service\Format;
use think\Db;

/**
 * 会员主表控制器
 * @package app\User\admin
 */
class Index extends Base
{
    /**
     * 会员主表列表
     * @author 似水星辰 [2630481389@qq.com]
     * @return mixed
     * @throws \think\exception\DbException
     */
    public function index()
    {
        cookie('__forward__', $_SERVER['REQUEST_URI']);

        // 查询
        $map = input('param.');
        $search_fields = [
            ['type' => 'text', 'name' => 'user_name', 'title' => '真实姓名', 'tips' => '请输入真实姓名','value'=>$map['user_name']],
            ['type' => 'text', 'name' => 'user_nickname', 'title' => '昵称', 'tips' => '请输入昵称','value'=>$map['user_nickname']],
            ['type' => 'text', 'name' => 'mobile', 'title' => '手机号', 'tips' => '请输入手机号','value'=>$map['mobile']],
            ['type' => 'select', 'name' => 'sex', 'title' => '性别', 'tips' => '','extra'=>['全部','男','女'],'value'=>$map['sex']],
        ];

        if($map['user_nickname']){
            $map1[]=['user_nickname','like','%'.$map['user_nickname'].'%'];
            unset($map['user_nickname']);
        }
        if($map['user_name']){
            $map1[]=['user_name','like','%'.$map['user_name'].'%'];
            unset($map['user_name']);
        }
        if($map['sex'] == 0){
            $map1[]=['sex','in','0,1,2'];
            unset($map['sex']);
        }
        // 排序
        $order = $this->getOrder();
        // 数据列表
        $data_list = UserModel::where($map)->where($map1)->order($order)->paginate();
        $fields = [
            ['id', 'ID'],
            ['user_name', '真实姓名'],
            ['user_nickname', '昵称'],
            ['head_img', '头像', 'picture'],
            ['sex', '性别', 'callback', 'get_sex'],
            ['mobile', '手机号'],
            ['user_money', '会员余额'],
            ['score', '会员积分', 'text'],
            ['user_level', '会员等级'],
            ['user_type', '会员类型','status','',['普通会员','白银会员','黄金会员']],
            ['total_consumption_money', '累计消费金额'],
            ['count_score', '累计获取积分'],
            ['right_button', '操作', 'btn']
        ];
        return Format::ins()//实例化
        ->addColumns($fields)//设置字段
        ->setTopSearch($search_fields)
        ->setTopButtons($this->top_button)
            ->setRightButtons($this->right_button)
            ->setData($data_list)//设置数据
            ->fetch();//显示
    }

    /**
     * 新增
     * @author 似水星辰 [2630481389@qq.com]
     * @return mixed
     */
    public function add()
    {
        // 保存数据
        if ($this->request->isPost()) {
            // 表单数据
            $data = $this->request->post();

            // 验证
            $result = $this->validate($data, 'User');
            if (true !== $result) $this->error($result);
            // 启动事务
            Db::startTrans();
            try {
                $result = UserModel::create($data);
                $id = $result->id;
                if (!$id) {
                    exception('新增会员失败');
                }
                // 新增会员附加信息
                $userinfo = Db::name('user_info')->insert(['user_id' => $id, 'invite_code' => 'IC00' . $id]);
                if (!$userinfo) {
                    exception('新增会员附加信息失败');
                }
                // 提交事务
                Db::commit();
            } catch (\Exception $e) {
                // 回滚事务
                Db::rollback();
                $this->error($e->getMessage());
            }
            $this->success('新增成功', cookie('__forward__'));
        }

        $fields = [
            ['type' => 'text', 'name' => 'mobile', 'title' => '手机号'],
            ['type' => 'password', 'name' => 'password', 'title' => '密码'],
            ['type' => 'text', 'name' => 'user_nickname', 'title' => '昵称'],
            ['type' => 'text', 'name' => 'user_name', 'title' => '姓名'],
            ['type' => 'image', 'name' => 'head_img', 'title' => '头像'],
        ];
        $this->assign('page_title', '新增会员主表');
        $this->assign('form_items', $fields);
        return $this->fetch('admin@public/add');
    }

    /**
     * 编辑
     * @param null $id 会员主表id
     * @author 似水星辰 [2630481389@qq.com]
     * @return mixed
     */
    public function edit($id = null)
    {
        if ($id === null) $this->error('缺少参数');

        // 保存数据
        if ($this->request->isPost()) {
            // 表单数据
            $data = $this->request->post();
            // 如果没有填写密码，则不更新密码
            if ($data['password'] == '') {
                unset($data['password']);
            }

            // 验证
            $result = $this->validate($data, 'User');
            if (true !== $result) $this->error($result);
            $UserModel = new UserModel();
            if ($UserModel->allowField([ 'user_nickname', 'password', 'head_img'])->update($data)) {
                $this->success('编辑成功', cookie('__forward__'));
            } else {
                $this->error('编辑失败');
            }
        }

        $info = UserModel::get($id);
        $fields = [
            ['type' => 'hidden', 'name' => 'id'],
            ['type' => 'text', 'name' => 'mobile', 'title' => '手机号', 'tips' => '', 'attr' => ''],
            ['type' => 'password', 'name' => 'password', 'title' => '密码', 'tips' => '', 'attr' => ''],
            ['type' => 'text', 'name' => 'user_nickname', 'title' => '昵称', 'tips' => '', 'attr' => ''],
            ['type' => 'text', 'name' => 'user_name', 'title' => '姓名', 'tips' => '', 'attr' => ''],
            ['type' => 'image', 'name' => 'head_img', 'title' => '头像', 'tips' => '', 'attr' => ''],
            //['type' => 'number', 'name' => 'user_level', 'title' => '会员等级', 'tips' => '', 'attr' => '', 'value' => '0'],
            //['type' => 'number', 'name' => 'user_type', 'title' => '会员类型', 'tips' => '1注册会员', 'attr' => '', 'value' => '1']
        ];
        $this->assign('page_title', '编辑会员主表');
        $this->assign('form_items', $this->setData($fields, $info));
        return $this->fetch('admin@public/edit');
    }

    /**
     * 设置状态
     * @param string $type 类型：disable/enable
     * @param array $record 行为日志内容
     * @return mixed|void
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @author 似水星辰 [ 2630481389@qq.com ]
     */
    public function setStatus($type = '')
    {
        $ids   = $this->request->isPost() ? input('post.ids/a') : input('param.ids');
        $ids   = (array)$ids;
        $field = input('param.field', 'status');

        empty($ids) && $this->error('缺少主键');


        $result = false;
        switch ($type) {
            case 'disable': // 禁用
                $result = UserModel::where('id','IN',$ids)->setField($field, 0);
                break;
            case 'enable': // 启用
                $result = UserModel::where('id','IN',$ids)->setField($field, 1);
                break;
            case 'delete': // 删除
                $result = UserModel::where('id','IN',$ids)->delete();
                break;
            default:
                $this->error('非法操作');
                break;
        }

        if (false !== $result) {
            // \Cache::clear();

            // 记录行为
            action_log('admin_user_'.$type, 'user', $ids, UID, 'ID：'.implode('、', $ids));
            $this->success('操作成功');
        } else {
            $this->error('操作失败');
        }
    }
}