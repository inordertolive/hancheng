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

use app\admin\model\Hook as HookModel;
use app\admin\model\HookAddons;
use service\Format;

/**
 * 钩子控制器
 * @package app\admin\controller
 */
class Hook extends Base
{
    /**
     * 钩子管理
     * @author 似水星辰 [ 2630481389@qq.com ]
     */
    public function index()
    {

        // 数据列表
        $data_list = HookModel::order('id desc')->paginate();

		$fields = [
			['name', '名称'],
            ['description', '描述'],
            ['plugin', '所属插件', 'callback', function($plugin){
                return $plugin == '' ? '系统' : $plugin;
            }],
            ['system', '系统钩子', 'status', '', ['否','是'],'text-center'],
            ['status', '状态', 'status','','','text-center'],
            ['right_button', '操作', 'btn','','','text-center']
		];
		return Format::ins() //实例化
			->setPageTitle('钩子管理') // 设置页面标题
			->addColumns($fields)//设置字段
			->setTopButtons($this->top_button)
			->setRightButtons($this->right_button)
			->setData($data_list)//设置数据
			->fetch();//显示
    }

    /**
     * 新增
     * @author 似水星辰 [ 2630481389@qq.com ]
     */
    public function add()
    {
        // 保存数据
        if ($this->request->isPost()) {
            // 表单数据
            $data = $this->request->post();
            $data['system'] = 0;

            // 验证
            $result = $this->validate($data, 'Hook');
            if(true !== $result) $this->error($result);

            if ($hook = HookModel::create($data)) {
                cache('hook_plugins', null);
                // 记录行为
                action_log('hook_add', 'hook', $hook['id'], UID, $data['name']);
                $this->success('新增成功', 'index');
            } else {
                $this->error('新增失败');
            }
        }

		$fields =[
			['type' => 'text', 'name' => 'name', 'title' => '钩子名称', 'tips' =>'由字母和下划线组成','attr' => 'data-rule="required;name;" data-rule-name="[/^[a-zA-Z][a-zA-Z0-9_]*$/, \'请输入正确的配置标识，只能使用英文和下划线，必须以英文字母开头\']" data-msg-required="钩子名称不能为空"'],
            ['type' => 'textarea', 'name' => 'description', 'title' => '钩子描述'],
            ['type' => 'radio', 'name' => 'status', 'title' => '状态', '', 'extra' => ['禁用', '启用'], 'value' => 1]
		];
		$this->assign('page_title','新增钩子');
		$this->assign('form_items', $fields);
        return $this->fetch('public/add');

    }

    /**
     * 编辑
     * @param int $id 钩子id
     * @author 似水星辰 [ 2630481389@qq.com ]
     * @return mixed
     */
    public function edit($id = 0)
    {
        if ($id === 0) $this->error('参数错误');

        // 保存数据
        if ($this->request->isPost()) {
            $data = $this->request->post();
            // 验证
            $result = $this->validate($data, 'Hook');
            if(true !== $result) $this->error($result);

            if ($hook = HookModel::update($data)) {
                // 调整插件顺序
                if ($data['sort'] != '') {
                    HookAddons::sort($data['name'], $data['sort']);
                }
                cache('hook_plugins', null);
                // 记录行为
                action_log('hook_edit', 'hook', $hook['id'], UID, $data['name']);
                $this->success('编辑成功', 'index');
            } else {
                $this->error('编辑失败');
            }
        }

        // 获取数据
        $info = HookModel::get($id);

        // 该钩子的所有插件
        $hooks = HookAddons::where('hook', $info['name'])->order('sort')->column('plugin');
        $hooks = parse_array($hooks);

		$fields =[
			['type' => 'hidden', 'name' => 'id', ],
			['type' => 'text', 'name' => 'name', 'title' => '钩子名称', 'tips' =>'由字母和下划线组成','attr' => 'data-rule="required;name;" data-rule-name="[/^[a-zA-Z][a-zA-Z0-9_]*$/, \'请输入正确的配置标识，只能使用英文和下划线，必须以英文字母开头\']" data-msg-required="钩子名称不能为空"'],
            ['type' => 'textarea', 'name' => 'description', 'title' => '钩子描述'],
			['type' => 'sort', 'name' => 'sort', 'title' => '插件排序', 'extra' => $hooks , 'value' => implode(',',$hooks)],
            ['type' => 'radio', 'name' => 'status', 'title' => '状态', '', 'extra' => ['禁用', '启用'], 'value' => 1]
		];
		$this->assign('page_title','编辑钩子');
		$this->assign('set_style',['/static/plugins/jquery-nestable/jquery.nestable.css']);
		$this->assign('set_script',['/static/plugins/jquery-nestable/jquery.nestable.js']);
		$this->assign('form_items', $this->setData($fields, $info));
        return $this->fetch('public/edit');
    }
}
