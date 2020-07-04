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

use app\admin\model\Menu as MenuModel;
use app\admin\model\Model as CustomModel;
use app\admin\model\Field;
use app\admin\model\Module as ModuleModel;
use service\Tree;
use think\Db;
use service\File;
use service\Format;

/**
 * 自定义表控制器
 * @package app\admin\admin
 */
class Model extends Base
{
    /**
     * 自定义表列表
     * $group string 分组
     */
    public function index($group = 'all')
    {
        cookie('__forward__', $_SERVER['REQUEST_URI']);
        //获取已安装的模块
        $list_group = ModuleModel::where('status', 1)->column('name,title');
        $list_group = array_merge(['all' => '所有'], $list_group);
        $tab_list = [];
        foreach ($list_group as $key => $value) {
            $tab_list[$key]['title'] = $value;
            $tab_list[$key]['url'] = url('index', ['group' => $key]);
        }

        if ($group != 'all') {
            $map['module'] = $group;
        }

        // 数据列表
        $data_list = CustomModel::where($map)->order('sort,id desc')->paginate();

        $fields = [
            ['id', 'ID'],
            ['title', '标题'],
            ['name', '标识'],
            ['table', '表名称'],
            ['type', '类型', 'status', '', ['空表', '文章', '商城', '会员']],
            ['create_time', '创建时间', '', '', '', 'text-center'],
            ['status', '状态', 'status', '', '', 'text-center'],
            ['right_button', '操作', 'btn', '', '', 'text-center']
        ];

        return Format::ins()//实例化
        ->setTabNav($tab_list, $group)//设置TAB分组
        ->addColumns($fields)//设置字段
        ->setTopButton(['title' => '新增', 'href' => ['add', ['group' => $group]], 'icon' => 'fa fa-plus pr5', 'class' => 'btn btn-sm mr5 btn-default btn-flat'])
            ->setRightButton(['title' => '字段管理', 'href' => ['admin/field/index', ['id' => '__id__', 'status' => '__status__']], 'icon' => 'fa fa-list-ul pr5', 'class' => 'btn btn-xs mr5 btn-default btn-flat'])
            ->setRightButton(['title' => '生成CURD', 'href' => ['admin/model/curd', ['id' => '__id__']], 'icon' => 'fa fa-paper-plane-o pr5', 'class' => 'btn btn-xs mr5 btn-default btn-flat ajax-get confirm'])
            ->setRightButtons($this->right_button)
            ->setData($data_list)//设置数据
            ->fetch();//显示
    }

    /**
     * 新增自定义表
     * $group string 分组
     * @return mixed
     */
    public function add($group = 'all')
    {
        // 保存数据
        if ($this->request->isPost()) {
            $data = $this->request->post();

            if ($data['table'] == '') {
                $data['table'] = config('database.prefix') . $data['name'];
            } else {
                $data['table'] = str_replace('#@__', config('database.prefix'), $data['table']);
            }

            // 验证
            $result = $this->validate($data, 'Model');
            if (true !== $result) $this->error($result);
            // 严格验证表是否存在
            if (table_exist($data['table'])) {
                $this->error('表已存在，创建失败');
            }

            // 启动事务
            Db::startTrans();
            try {
                $model = CustomModel::create($data);
                if (!$model) {
                    throw new \think\Exception('新增失败', 100006);
                }

                // 创建表
                $result = CustomModel::createTable($model);
                if (false === $result) {
                    throw new \think\Exception('创建表失败', 100006);
                }
                \Cache::clear();
                // 记录行为
                action_log('admin_model_add', 'admin_model', $model['id'], UID, $data['title']);
                // 提交事务
                Db::commit();
            } catch (\Exception $e) {
                // 回滚事务
                Db::rollback();
                $msg = $e->getMessage();
                $this->error($msg);
            }

            $this->success('新增成功', cookie('__forward__'));
        }
        //获取已安装的模块
        $ModuleModel = ModuleModel::where('status', 1)->column('name,title');
        $ModuleModel = array_merge(['all' => '所有'], $ModuleModel);
		if($group != 'all'){
			$tree_where = $group;
		}

        $type_tips = '此选项添加后不可更改。空白表只创建id字段，文章附表需要arc_document主表支持，商城附表需要shop主表支持，会员附表需要user主表支持。所有表都会自动创建create_time和update_time字段';
        $fields = [
            ['type' => 'radio', 'name' => 'module', 'title' => '所属模块', 'tips' => '添加后不可修改，请谨慎选择', 'extra' => $ModuleModel, 'value' => $group],
            ['type' => 'text', 'name' => 'name', 'title' => '标识', 'tips' => '由小写字母、数字或下划线组成，不能以数字开头。标识即为表名，请勿带表前缀', 'attr' => 'data-rule="required;name;" data-rule-name="[/^[a-zA-Z][a-zA-Z0-9_]*$/, \'请输入正确的标识，只能使用英文和下划线，必须以英文字母开头\']" data-msg-required="标识不能为空"'],
            ['type' => 'text', 'name' => 'title', 'title' => '名称', 'tips' => '可填写中文'],
            ['type' => 'text', 'name' => 'cname', 'title' => '自定义控制器名', 'tips' => '自定义控制器名，仅生成CURD使用，必须使用英文，首字母大写'],
            ['type' => 'select', 'name' => 'menu_pid', 'title' => '所属菜单', 'tips' => '生成CURD时会自动在此菜单下生成列表，新增，编辑，禁用启用，删除菜单，如果不添加菜单，请忽略即可', 'extra' => $this->getMenuTree(0,'',$tree_where )],
            ['type' => 'hidden', 'name' => 'type', 'value' => 0],
            ['type' => 'text', 'name' => 'sort', 'title' => '排序', 'value' => 100],
            ['type' => 'radio', 'name' => 'status', 'title' => '立即启用', 'extra' => ['否', '是'], 'value' => 1],
        ];
        $this->assign('page_title', '新增表');
        $this->assign('form_items', $fields);
        return $this->fetch('public/add');
    }

    /**
     * 编辑自定义表
     * @param null $id 模型id
     * @return mixed
     */
    public function edit($id = null)
    {
        if ($id === null) $this->error('参数错误');

        // 保存数据
        if ($this->request->isPost()) {
            $data = $this->request->post();

            // 验证
            $result = $this->validate($data, 'Model.edit');
            if (true !== $result) $this->error($result);

            if (CustomModel::update($data)) {
                cache('admin_model_list', null);
                cache('admin_model_title_list', null);
                // 记录行为
                action_log('admin_model_edit', 'admin_model', $id, UID, "ID({$id}),标题({$data['title']})");
                $this->success('编辑成功', cookie('__forward__'));
            } else {
                $this->error('编辑失败');
            }
        }

        // 模型信息
        $info = CustomModel::get($id);
        //获取已安装的模块
        $ModuleModel = ModuleModel::where('status', 1)->column('name,title');
        $ModuleModel = array_merge(['all' => '所有'], $ModuleModel);

        $fields = [
            ['type' => 'hidden', 'name' => 'id'],
            ['type' => 'radio', 'name' => 'module', 'title' => '所属模块', 'extra' => $ModuleModel, 'tips' => '禁止修改', 'attr' => 'disabled'],
            ['type' => 'text', 'name' => 'name', 'title' => '标识', 'tips' => '禁止修改', 'attr' => 'readonly'],
            ['type' => 'text', 'name' => 'title', 'title' => '名称', 'tips' => '可填写中文'],
            ['type' => 'text', 'name' => 'cname', 'title' => '自定义控制器名', 'tips' => '禁止修改', 'attr' => 'disabled'],
            ['type' => 'select', 'name' => 'menu_pid', 'title' => '所属菜单', 'tips' => '生成CURD时会自动在此菜单下生成列表，新增，编辑，禁用启用，删除菜单，如果不添加菜单，请忽略即可', 'extra' => $this->getMenuTree()],
            ['type' => 'text', 'name' => 'sort', 'title' => '排序'],
            ['type' => 'radio', 'name' => 'status', 'title' => '立即启用', 'extra' => ['否', '是']],
        ];
        $this->assign('page_title', '编辑表(' . $info['name'] . ')');
        $this->assign('form_items', $this->setData($fields, $info));
        return $this->fetch('public/edit');
    }

    /**
     * 删除自定义表
     * @param null $ids 模型id组
     * @return mixed|void
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @throws \think\exception\PDOException
     */
    public function delete($ids = null)
    {
        if ($ids === null) $this->error('参数错误');

        $model = CustomModel::where('id', $ids)->find();
        if ($model['system'] == 1) {
            $this->error('系统模型,禁止删除!');
        }
        // 删除表和字段信息
        if (CustomModel::deleteTable($ids)) {

            // 删除字段数据
            if (false !== Db::name('admin_model_field')->where('model', $ids)->delete()) {
                cache('admin_model_list', null);
                cache('admin_model_title_list', null);
                return parent::delete();
            } else {
                return $this->error('删除模型字段失败');
            }
        } else {
            return $this->error('删除模型表失败');
        }
    }

    /**
     * 获取树形菜单
     * @param int $id 需要隐藏的菜单id
     * @param string $default 默认第一个菜单项，默认为“顶级菜单”，如果为false则不显示，也可传入其他名称
     * @param string $module 模型名
     * @return mixed
     * @author 似水星辰 [ 2630481389@qq.com ]
     */
    public function getMenuTree($id = 0, $default = '', $module = '')
    {
        $where[] = ['status', 'egt', 0];
        if ($module != '') {
            $where1['module'] = $module;
        }

        // 排除指定菜单及其子菜单
        if ($id !== 0) {
            $hide_ids = array_merge([$id], MenuModel::getChildsId($id));
            $where[] = ['id', 'not in', $hide_ids];
        }

        // 获取菜单
        $menus = Tree::toList(MenuModel::where($where)->where($where1)->order('pid,id')->column('id,pid,title'),0,0,2);
        foreach ($menus as $menu) {
            $result[$menu['id']] = $menu['title_display'];
        }

        // 设置默认菜单项标题
        if ($default != '') {
            $result[0] = $default;
        }

        // 隐藏默认菜单项
        if ($default === false) {
            unset($result[0]);
        }

        return $result;
    }

    public function curd($id = 0)
    {
        /*if(in_array($id,[1,2,3])){
            $this->error('此模型禁止生成CURD');
        }*/
        // 模型信息
        $info = CustomModel::get($id);
        if ($info['is_make'] == 1) {
            $this->error('此表已经生成过文件，请勿重新生成');
        }
        if ($info['type'] == 0) {
            $where['model'] = $id;
        } else {
            $where1[] = ['model', 'in', [1, $id]];
        }

        // 获取模型字段
        $where['status'] = 1;
        $where['show'] = 1;

        //组装列表字段
        $fields = '';
        $field = Field::where($where)->where($where1)->order('sort asc,id asc')->column('id,name,title,type,value,extra');
        $extra_field = [['name' => 'aid', 'title' => 'ID']];
        $field = array_merge($extra_field, $field);
        foreach ($field as $f) {
            $fields .= "[";
            $fields .= "'" . $f['name'] . "', '" . $f['title'] . "', ";
            if (($f['type'] == 'radio' || $f['type'] == 'select' || $f['type'] == 'checkbox') && $f['extra'] != '') {
                $fields .= "'status', ";
            } else {
                $fields .= "'', ";
            }

            if ($f['value'] != '') {
                $fields .= "'" . $f['value'] . "', ";
            } else {
                $fields .= "'', ";
            }

            if (($f['type'] == 'radio' || $f['type'] == 'select' || $f['type'] == 'checkbox') && $f['extra'] != '') {
                $str = $this->varexport(parse_attr($f['extra']), true);
                $fields .= $str;
            } else {
                $fields .= "'', ";
            }

            if ($f['class'] != '') {
                $fields .= "'" . $f['class'] . "', ";
            }

            $fields .= "],\r\n";
        }

        //组装新增字段
        $fields_add = '';
        $field_add = Field::where($where)->order('sort asc,id asc')->column(true);
        foreach ($field_add as $a) {
            $fields_add .= "[";
            $fields_add .= "'type'=>'" . $a['type'] . "', 'name'=>'" . $a['name'] . "', 'title'=>'" . $a['title'] . "', ";
            if ($a['tips']) {
                $fields_add .= "'tips'=>'" . $a['tips'] . "', ";
            }
            if ($a['value'] != '') {
                $fields_add .= "'value'=>'" . $a['value'] . "', ";
            }
            if (($a['type'] == 'radio' || $a['type'] == 'select' || $a['type'] == 'checkbox') && $a['extra'] != '') {
                $str = $this->varexport(parse_attr($a['extra']), true);
                $fields_add .= "'extra'=>" . $str;
            }

            $fields_add .= "]," . PHP_EOL;
        }

        //控制器文件路径
        $c_file_name = $info['cname'] ? $info['cname'] : ucwords(strtolower($info['name']));
        $c_file_dir = APP_PATH . $info['module'] . '/' . 'admin' . '/' . $c_file_name . '.php';
        $c_file_content = File::read_file(ROOT_PATH . 'data/page/controller.tpl');
        $c_file_content = str_replace(['{module}', '{model}', '{title}', '{fields}', '{fields_add}'], [$info['module'], $c_file_name, $info['title'], $fields, $fields_add], $c_file_content);

        //模型文件路径
        $m_file_dir = APP_PATH . $info['module'] . '/' . 'model' . '/' . $c_file_name . '.php';
        $m_file_content = File::read_file(ROOT_PATH . 'data/page/model.tpl');
        $m_file_content = str_replace(['{module}', '{model}', '{title}', '{table}'], [$info['module'], $c_file_name, $info['title'], strtoupper($info['name'])], $m_file_content);

        //验证器路径
        $v_file_dir = APP_PATH . $info['module'] . '/' . 'validate' . '/' . $c_file_name . '.php';
        $v_file_content = File::read_file(ROOT_PATH . 'data/page/validate.tpl');
        $v_file_content = str_replace(['{module}', '{model}', '{title}'], [$info['module'], $c_file_name, $info['title']], $v_file_content);
        $file = [$c_file_dir, $m_file_dir, $v_file_dir];

        $res = File::create_dir_or_files($file);
        if ($res) {
            $res1 = File::write_file($c_file_dir, $c_file_content);
            $res2 = File::write_file($m_file_dir, $m_file_content);
            $res3 = File::write_file($v_file_dir, $v_file_content);

            if ($res1 && $res2 && $res3) {
                if ($info['menu_pid']) {
                    $menu['module'] = $info['module'];
                    $menu['pid'] = $info['menu_pid'];
                    $menu['title'] = $info['title'];
                    $menu['url_value'] = $info['module'] . '/' . $info['cname'] . '/index';
                    $MenuModel = new MenuModel;
                    if ($result = $MenuModel->allowField(true)->create($menu)) {
                        $menu['child_node'] = 'add,edit,delete,setstatus';
                        // 自动创建子菜单
                        $this->createChildNode($menu, $result['id']);
                    }
                    \Cache::clear();
                }

                CustomModel::where('id', $id)->update(['is_make' => 1, 'menu_id' => $result['id']]);
                $this->success('生成成功');
            }
        }

    }

    /**
     * 数组短标签格式化
     * @param $expression
     * @param bool $return
     * @author 似水星辰 [ 2630481389@qq.com ]
     * @created 2019/10/7 17:40
     */
    public function varexport($expression, $return = FALSE)
    {
        $export = var_export($expression, TRUE);
        $patterns = [
            "/array \(/" => '[',
            "/^([ ]*)\)(,?)$/m" => '$1]$2',
            "/=>[ ]?\n[ ]+\[/" => '=> [',
            "/([ ]*)(\'[^\']+\') => ([\[\'])/" => '$1$2 => $3',
        ];
        $export = preg_replace(array_keys($patterns), array_values($patterns), $export);
        if ((bool)$return) return $export; else echo $export;
    }

    /**
     * 添加子菜单
     * @param array $data 菜单数据
     * @param string $pid 上级菜单id
     * @author 似水星辰 [ 2630481389@qq.com ]
     */
    private function createChildNode($data = [], $pid = '')
    {
        $url_value = substr($data['url_value'], 0, strrpos($data['url_value'], '/')) . '/';
        $child_node = [];
        $data['pid'] = $pid;
        $menus = explode(',', $data['child_node']);
        foreach ($menus as $item) {
            switch ($item) {
                case 'add':
                    $data['title'] = '新增';
                    break;
                case 'edit':
                    $data['title'] = '编辑';
                    break;
                case 'delete':
                    $data['title'] = '删除';
                    break;
                case 'setstatus':
                    $data['title'] = '设置状态';
                    break;
            }
            $data['url_value'] = $url_value . $item;
            $data['sort'] = 100;
            $data['create_time'] = $this->request->time();
            $data['update_time'] = $this->request->time();
            unset($data['child_node'], $data['auto_create'], $data['role']);
            $child_node[] = $data;
        }

        if ($child_node) {
            $MenuModel = new MenuModel();
            $MenuModel->insertAll($child_node);
        }
    }
}