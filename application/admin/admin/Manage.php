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

use app\admin\model\Manage as ManageModel;
use app\admin\model\Role as RoleModel;
use service\Format;

/**
 * 管理员控制器
 * @package app\admin\admin
 */
class Manage extends Base
{
    /**
     * 用户首页
     * @return mixed
     */
    public function index()
    {
        cookie('__forward__', $_SERVER['REQUEST_URI']);

        // 非超级管理员检查可管理角色
        if (session('admin_auth.role') != 1) {
            $role_list = RoleModel::getChildsId(session('admin_auth.role'));
            $map[] = ['role','in', $role_list];
        }

        // 数据列表
        $data_list = ManageModel::where($map)->order('sort,role,id desc')->paginate();
        
        // 角色列表
        if (session('admin_auth.role') != 1) {
            $role_list = RoleModel::getTree(null, false, session('admin_auth.role'));
        } else {
            $role_list = RoleModel::getTree();
        }
		$fields =[
			['id', 'ID'],
            ['username', '用户名'],
            ['nickname', '昵称'],
            ['role', '角色', $role_list],
            ['email', '邮箱'],
            ['mobile', '手机号','','','','text-center'],
            ['create_time', '创建时间','','','','text-center'],
            ['status', '状态', 'status','','','text-center'],
            ['right_button', '操作', 'btn','','','text-center']
		];

		return Format::ins() //实例化
			->addColumns($fields)//设置字段
			->setTopButtons($this->top_button)
			->setRightButtons($this->right_button)
			->setData($data_list)//设置数据
			->fetch();//显示
    }

    /**
     * 新增
     * @author 似水星辰 [ 2630481389@qq.com ]
     * @return mixed
     */
    public function add()
    {
        // 保存数据
        if ($this->request->isPost()) {
            $data = $this->request->post();
            // 验证
            $result = $this->validate($data, 'Manage');
            // 验证失败 输出错误信息
            if(true !== $result) $this->error($result);

            // 非超级管理需要验证可选择角色
            if (session('admin_auth.role') != 1) {
                if ($data['role'] == session('admin_auth.role')) {
                    $this->error('禁止创建与当前角色同级的用户');
                }
                $role_list = RoleModel::getChildsId(session('admin_auth.role'));
                if (!in_array($data['role'], $role_list)) {
                    $this->error('权限不足，禁止创建非法角色的用户');
                }
            }

            if ($admin = ManageModel::create($data)) {
                // 记录行为
                action_log('admin_add', 'admin', $admin->id, UID, ' 用户名：'.$data['username'].' 昵称：'.$data['nickname'].' ID：'.$admin->id);
                $this->success('新增成功', url('index'));
            } else {
                $this->error('新增失败');
            }
        }

        // 角色列表
        if (session('admin_auth.role') != 1) {
            $role_list = RoleModel::getTree(null, false, session('admin_auth.role'));
        } else {
            $role_list = RoleModel::getTree(null, false);
        }

		$fields =[
			['type' => 'text', 'name' => 'username', 'title' => '用户名', 'tips' => '必填，可由英文字母、数字组成,，必须使用英文字母开头', 'attr' => 'data-rule="required;username;" data-rule-username="[/^[\w\d]{3,12}$/, \'请输入正确的用户名\']" data-msg-required="用户名不能为空"'],
            ['type' => 'text', 'name' => 'nickname', 'title' => '昵称', 'tips' => '可以是中文', 'attr' => 'data-rule="required;" data-msg-required="昵称不能为空"'],
            ['type' => 'select', 'name' => 'role', 'title' => '角色', 'tips' => '', 'extra' => $role_list],
            ['type' => 'text', 'name' => 'email', 'title' => '邮箱', 'tips' => ''],
            ['type' => 'password', 'name' => 'password', 'title' => '密码', 'tips' => '必填，6-20位'],
            ['type' => 'text', 'name' => 'mobile', 'title' => '手机号'],
            ['type' => 'image', 'name' => 'avatar', 'title' => '头像'],
            ['type' => 'radio', 'name' => 'status', 'title' => '状态', '', 'extra' => ['禁用', '启用'], 'value' => 1]
		];
		$this->assign('page_title','新增管理员');
		$this->assign('form_items',$fields);
        return $this->fetch('public/add');
    }

    /**
     * 编辑
     * @param null $id 用户id
     * @return mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @author 似水星辰 [ 2630481389@qq.com ]
     */
    public function edit($id = null)
    {
        if ($id === null) $this->error('缺少参数');

        // 非超级管理员检查可编辑用户
        if (session('admin_auth.role') != 1) {
            $role_list = RoleModel::getChildsId(session('admin_auth.role'));
            $map[] = ['role','in', $role_list];
            $user_list = ManageModel::where($map)->column('id');
            if (!in_array($id, $user_list)) {
                $this->error('权限不足，没有可操作的用户');
            }
        }

        // 保存数据
        if ($this->request->isPost()) {
            $data = $this->request->post();

            // 禁止修改超级管理员的角色和状态
            if ($data['id'] == 1 && $data['role'] != 1) {
                $this->error('禁止修改超级管理员角色');
            }

            // 禁止修改超级管理员的状态
            if ($data['id'] == 1 && $data['status'] != 1) {
                $this->error('禁止修改超级管理员状态');
            }

            // 验证
            $result = $this->validate($data, 'Manage.update');
            // 验证失败 输出错误信息
            if(true !== $result) $this->error($result);

            // 如果没有填写密码，则不更新密码
            if ($data['password'] == '') {
                unset($data['password']);
            }

            // 非超级管理需要验证可选择角色
            if (session('admin_auth.role') != 1) {
                if ($data['role'] == session('admin_auth.role')) {
                    $this->error('禁止修改为当前角色同级的用户');
                }
                $role_list = RoleModel::getChildsId(session('admin_auth.role'));
                if (!in_array($data['role'], $role_list)) {
                    $this->error('权限不足，禁止修改为非法角色的用户');
                }
            }

            if (ManageModel::update($data)) {
                $admin = ManageModel::get($data['id']);
                // 记录行为
                action_log('admin_edit', 'admin', $admin['id'], UID, get_adminname($admin['id']));
                $this->success('编辑成功', cookie('__forward__'));
            } else {
                $this->error('编辑失败');
            }
        }

        // 获取数据
        $info = ManageModel::where('id', $id)->field('password', true)->find();

        // 角色列表
        if (session('admin_auth.role') != 1) {
            $role_list = RoleModel::getTree(null, false, session('admin_auth.role'));
        } else {
            $role_list = RoleModel::getTree(null, false);
        }
		$fields =[
			['type' => 'hidden', 'name' => 'id'],
			['type' => 'text', 'name' => 'username', 'title' => '用户名', 'attr' => 'readonly','tips' => '用户名不能修改'],
            ['type' => 'text', 'name' => 'nickname', 'title' => '昵称', 'tips' => '可以是中文', 'attr' => 'data-rule="required;" data-msg-required="昵称不能为空"'],
            ['type' => 'select', 'name' => 'role', 'title' => '角色', 'tips' => '', 'extra' => $role_list],
            ['type' => 'text', 'name' => 'email', 'title' => '邮箱', 'tips' => ''],
            ['type' => 'password', 'name' => 'password', 'title' => '密码', 'tips' => '必填，6-20位'],
            ['type' => 'text', 'name' => 'mobile', 'title' => '手机号'],
            ['type' => 'image', 'name' => 'avatar', 'title' => '头像'],
            ['type' => 'radio', 'name' => 'status', 'title' => '状态', '', 'extra' => ['禁用', '启用'], 'value' => 1]
		];

		$this->assign('page_title','编辑管理员');
		$this->assign('form_items', $this->setData($fields, $info));
        return $this->fetch('public/edit');
    }


    /**
     * 设置用户状态：删除、禁用、启用
     * @param string $type 类型：delete/enable/disable
     * @author 似水星辰 [ 2630481389@qq.com ]
     * @return mixed
     */
    public function setStatus($type = '')
    {
        $ids        = $this->request->isPost() ? input('post.ids/a') : input('param.ids');
        if ((is_array($ids) && in_array(UID, $ids)) || $ids == UID) {
            $this->error('禁止操作当前账号');
        }
		
        // 非超级管理员检查可管理用户
        if (session('admin_auth.role') != 1) {
            $user_ids  = (array)$ids;
            $role_list = RoleModel::getChildsId(session('admin_auth.role'));
            $map[] = ['role', 'in', $role_list];
            $user_list = ManageModel::where($map)->column('id');
            $user_list = array_intersect($user_list, $user_ids);
            if (!$user_list) {
                $this->error('权限不足，没有可操作的用户');
            } else {
                $this->request->post(['ids'=> $user_list]);
            }
        }
        $uid_delete = is_array($ids) ? '' : $ids;
        $ids        = array_map('get_adminname', (array)$ids);
        return parent::setStatus($type, ['admin_'.$type, 'admin', $uid_delete, UID, implode('、', $ids)]);
    }

}
