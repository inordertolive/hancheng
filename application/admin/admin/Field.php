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

use app\admin\model\Field as FieldModel;
use service\Format;

/**
 * 字段管理控制器
 * @package app\cms\admin
 */
class Field extends Base
{
    /**
     * 字段列表
     * @param int $id 文档模型id
     * @author 似水星辰 [ 2630481389@qq.com ]
     */
    public function index($id = 0, $status = 1)
    {
        if(!$id){ 
			$this->error('参数错误');
		}

		if($status == 0){
            $tip="自定义表的状态为禁用，此状态下修改字段无法保存，请启用表后再修改字段！";
        }

        cookie('__forward__', $_SERVER['REQUEST_URI']);

        $map['model'] = $id;
        // 数据列表
        $data_list = FieldModel::where($map)->order('sort asc')->paginate();
		$fields =[
			['id', 'ID'],
            ['name', '名称'],
            ['title', '标题'],
            ['type', '类型', 'text', '', config('form_item_type')],
            ['create_time', '创建时间','','','text-center'],
            ['sort', '排序', 'text.edit'],
            ['show', '显示', 'status','',['否','是'],'text-center'],
            ['status', '状态', 'status','','','text-center'],
            ['right_button', '操作', 'btn','','','text-center']
		];

		return Format::ins() //实例化
            ->setPageTips($tip)
			->setTableName('admin_model_field')
			->addColumns($fields)//设置字段
			->setTopButton(['title'=>'新增','href'=>['add',['model' => $id]],'icon'=>'fa fa-plus pr5','class'=>'btn btn-sm mr5 btn-success btn-flat'])
			->setRightButtons($this->right_button)
            ->replaceRightButton(['fixed' => 1], '', ['edit'])
			->setData($data_list)//设置数据
			->fetch();//显示
    }

    /**
     * 新增字段
     * @param string $model 文档模型id
     * @author 似水星辰 [ 2630481389@qq.com ]
     * @return mixed
     */
    public function add($model = '')
    {
        $model_type = get_model_type($model);

        if ($this->request->isPost()) {
            // 表单数据
            $data = $this->request->post();
			$data['verify'] = htmlspecialchars($data['verify']);
            // 验证字段名称是否为id
            if ($model_type == 0) {
                // 验证新增的字段是否被系统占用
                if ($data['name'] == 'id' /*|| is_default_field($data['name'])*/) {
                    $this->error('字段名称已存在');
                }
            }

            $result = $this->validate($data, 'Field');
            if(true !== $result) $this->error($result);

            if ($field = FieldModel::create($data)) {
                $FieldModel = new FieldModel();
                // 添加字段
                if ($FieldModel->newField($data)) {
                    // 清除缓存
                    cache('admin_system_fields', null);
                    // 记录行为
                    $details    = '表名('.get_model_title($data['model']).')、字段名称('.$data['name'].')、字段标题('.$data['title'].')、字段类型('.$data['type'].')';
                    action_log('admin_model_field_add', 'admin_model_field', $field['id'], UID, $details);
                    $this->success('新增成功', cookie('__forward__'));
                } else {
                    // 添加失败，删除新增的数据
                    FieldModel::destroy($field['id']);
                    $this->error($FieldModel->getError());
                }
            } else {
                $this->error('新增失败');
            }
        }

		$fields = [
			['type' => 'hidden', 'name' => 'model', 'value' => $model],
            ['type' => 'text', 'name' => 'name', 'title' => '字段标识', 'tips' => '由小写英文字母和下划线组成', 'attr' => 'data-rule="required;name;" data-rule-name="[/^[a-zA-Z][a-zA-Z0-9_]*$/, \'请输入正确的标识，只能使用英文和下划线，必须以英文字母开头\']" data-msg-required="标识不能为空"'],
            ['type' => 'text', 'name' => 'title', 'title' => '字段名称', 'tips' => '可填写中文', 'attr' => 'data-rule="required;"'],
            ['type' => 'select', 'name' => 'type', 'title' => '字段类型', 'extra' => config('form_item_type')],
            ['type' => 'text', 'name' => 'define', 'title' => '字段定义', 'tips' => '可根据实际需求自行填写或修改，但必须是正确的sql语法', 'value' => 'int(11) UNSIGNED NOT NULL'],
            ['type' => 'text', 'name' => 'value', 'title' => '字段默认值', 'value' => 0],
			['type' => 'text', 'name' => 'callback', 'title' => '回调方法', 'tips' => '如果某些字段的值需要特别处理，可以设置回调方法，例如：get_nickname'],
            ['type' => 'textarea', 'name' => 'extra', 'title' => '配置选项', 'tips' => '用于单选、多选、下拉、联动等类型，一行一个，例如：1:西瓜 2:黄瓜'],
			['type' => 'text', 'name' => 'verify', 'title' => '验证规则', 'tips' => '仅为nice-validator验证，具体规则请了解nice-validator，如需更多，请手动设置TP验证器，例如：data-rule="required;name;" data-rule-name="[/^[a-zA-Z][a-zA-Z0-9_]*$/, \'请输入正确的标识，只能使用英文和下划线，必须以英文字母开头\']" data-msg-required="标识不能为空"'],
            ['type' => 'text', 'name' => 'attr', 'title' => '额外参数', 'tips' => 'html元素上的额外参数，例如：aa="bb"'],
            ['type' => 'textarea', 'name' => 'tips', 'title' => '字段说明', 'tips' => '字段详细说明'],
            ['type' => 'radio', 'name' => 'fixed', 'title' => '是否为固定字段', 'tips' => '如果为 固定字段 则添加后不可修改', 'extra' => ['否', '是'], 'value' => 0],
            ['type' => 'radio', 'name' => 'show', 'title' => '是否显示', 'tips' => '新增或编辑时是否显示该字段', 'extra' => ['否', '是'], 'value' => 1],
            ['type' => 'radio', 'name' => 'status', 'title' => '立即启用', 'extra' => ['否', '是'], 'value' => 1],
            ['type' => 'text', 'name' => 'sort', 'title' => '排序', 'value' => 100],
		];
		$this->assign('page_title','新增字段');
		$this->assign('set_script',['/static/admin/js/field.js']);
		$this->assign('form_items',$fields);
        return $this->fetch('public/add');
    }

    /**
     * 编辑字段
     * @param null $id 字段id
     * @author 似水星辰 [ 2630481389@qq.com ]
     * @return mixed
     */
    public function edit($id = null)
    {
        if ($id === null) $this->error('参数错误');

        // 保存数据
        if ($this->request->isPost()) {
            // 表单数据
            $data = $this->request->post();
			$data['verify'] = htmlspecialchars($data['verify']);
            // 验证
            $result = $this->validate($data, 'Field');
            if(true !== $result) $this->error($result);

            // 更新字段信息
            $FieldModel = new FieldModel();
            if ($FieldModel->updateField($data)) {
                if ($FieldModel->isUpdate(true)->save($data)) {
                    // 记录行为
                    action_log('admin_model_field_edit', 'admin_model_field', $id, UID, $data['name']);
                    $this->success('字段更新成功', cookie('__forward__'));
                }
            }
            $this->error('字段更新失败');
        }

        // 获取数据
        $info = FieldModel::get($id);
		$fields = [
			['type' => 'hidden', 'name' => 'id'],
			['type' => 'hidden', 'name' => 'model'],
            ['type' => 'text', 'name' => 'name', 'title' => '字段标识', 'tips' => '由小写英文字母和下划线组成', 'attr' => 'data-rule="required;name;" data-rule-name="[/^[a-zA-Z][a-zA-Z0-9_]*$/, \'请输入正确的标识，只能使用英文和下划线，必须以英文字母开头\']" data-msg-required="标识不能为空"'],
            ['type' => 'text', 'name' => 'title', 'title' => '字段名称', 'tips' => '可填写中文', 'attr' => 'data-rule="required;"'],
            ['type' => 'select', 'name' => 'type', 'title' => '字段类型', 'extra' => config('form_item_type')],
            ['type' => 'text', 'name' => 'define', 'title' => '字段定义', 'tips' => '可根据实际需求自行填写或修改，但必须是正确的sql语法', 'value' => 'varchar(256) NOT NULL'],
            ['type' => 'text', 'name' => 'value', 'title' => '字段默认值'],
			['type' => 'text', 'name' => 'callback', 'title' => '回调方法', 'tips' => '如果某些字段的值需要特别处理，可以设置回调方法，例如：get_nickname'],
            ['type' => 'textarea', 'name' => 'extra', 'title' => '配置选项', 'tips' => '用于单选、多选、下拉、联动等类型，一行一个，例如：1:西瓜 2:黄瓜'],
			['type' => 'text', 'name' => 'verify', 'title' => '验证规则', 'tips' => '仅为nice-validator验证，具体规则请了解nice-validator，如需更多，请手动设置TP验证器，例如：data-rule="required;name;" data-rule-name="[/^[a-zA-Z][a-zA-Z0-9_]*$/, \'请输入正确的标识，只能使用英文和下划线，必须以英文字母开头\']" data-msg-required="标识不能为空"'],
            ['type' => 'text', 'name' => 'attr', 'title' => '额外参数', 'tips' => 'html元素上的额外参数，例如：aa="bb"'],
            ['type' => 'textarea', 'name' => 'tips', 'title' => '字段说明', 'tips' => '字段详细说明'],
            ['type' => 'radio', 'name' => 'fixed', 'title' => '是否为固定字段', 'tips' => '如果为 <code>固定字段</code> 则添加后不可修改', 'extra' => ['否', '是'], 'value' => 0],
            ['type' => 'radio', 'name' => 'show', 'title' => '是否显示', 'tips' => '新增或编辑时是否显示该字段', 'extra' => ['否', '是'], 'value' => 1],
            ['type' => 'radio', 'name' => 'status', 'title' => '立即启用', 'extra' => ['否', '是'], 'value' => 1],
            ['type' => 'text', 'name' => 'sort', 'title' => '排序', 'value' => 100],
		];
		$this->assign('page_title','编辑字段');
		$this->assign('set_script',['/static/admin/js/field.js']);
		$this->assign('form_items',$this->setData($fields,$info));
        return $this->fetch('public/edit');
    }

    /**
     * 删除字段
     * @param null $ids 字段id
     * @author 似水星辰 [ 2630481389@qq.com ]
     * @return mixed
     */
    public function delete($ids = null)
    {
        if ($ids === null) $this->error('参数错误');

        $FieldModel = new FieldModel();
        $field      = $FieldModel->where('id', $ids)->find();

        if ($FieldModel->deleteField($field)) {
            if ($FieldModel->where('id', $ids)->delete()) {
                // 记录行为
                $details = '表名('.get_model_title($field['model']).')、字段名称('.$field['name'].')、字段标题('.$field['title'].')、字段类型('.$field['type'].')';
                action_log('admin_model_field_delete', 'admin_model_field', $ids, UID, $details);
                $this->success('删除成功', cookie('__forward__'));
            }
        }
        return $this->error('删除失败');
    }

	/**
     * 快速编辑
     * @param array $record 行为日志
     * @author 似水星辰 [ 2630481389@qq.com ]
     * @return mixed
     */
    public function quickEdit($record = [])
    {
        $id      = input('post.pk', '');
        $field   = input('post.name', '');
        $value   = input('post.value', '');
        $config  = FieldModel::where('id', $id)->value($field);
        $details = '字段(' . $field . ')，原值(' . $config . ')，新值：(' . $value . ')';
        return parent::quickEdit(['admin_model_field_edit', 'admin_model_field', $id, UID, $details]);
    }
}
