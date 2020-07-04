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

use app\admin\model\Addons as AddonsModel;
use app\admin\model\HookAddons as HookAddonsModel;
use think\Db;
use service\Sql;
use service\Format;

/**
 * 插件管理控制器
 * @package app\admin\controller
 */
class Addons extends Base
{
    /**
     * 首页
     * @param string $group 分组
     * @author 似水星辰 [ 2630481389@qq.com ]
     * @return mixed
     */
    public function index($group = 'local')
    {
        // 配置分组信息
        $list_group = ['local' => '本地插件'];
        foreach ($list_group as $key => $value) {
            $tab_list[$key]['title'] = $value;
            $tab_list[$key]['url'] = url('index', ['group' => $key]);
        }

        switch ($group) {
            case 'local':
                // 查询条件
                $keyword = $this->request->get('keyword', '');

                if (input('?param.status') && input('param.status') != '_all') {
                    $status = input('param.status');
                } else {
                    $status = '';
                }

                $AddonsModel = new AddonsModel;
                $result = $AddonsModel->getAll($keyword, $status);

                if ($result['addons'] === false) {
                    $this->error($AddonsModel->getError());
                }


                $this->assign('page_title', '插件管理');
                $this->assign('addons', $result['addons']);
                $this->assign('total', $result['total']);
                $this->assign('tab_nav', ['tab_list' => $tab_list, 'active' => $group]);
                return $this->fetch();
                break;
            case 'online':
                break;
        }
    }

    /**
     * 安装插件
     * @param string $name 插件标识
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @author 似水星辰 [ 2630481389@qq.com ]
     */
    public function install($name = '')
    {
        // 设置最大执行时间和内存大小
        ini_set('max_execution_time', '0');
        ini_set('memory_limit', '1024M');

        $addons_name = trim($name);
        if ($addons_name == '') $this->error('插件不存在！');

        $addons_class = get_addons_class($addons_name);

        if (!class_exists($addons_class)) {
            $this->error('插件不存在！');
        }

        // 实例化插件
        $addons = new $addons_class;
        // 插件预安装
        if (!$addons->install()) {
            $this->error('插件预安装失败!原因：' . $addons->getError());
        }

        // 添加钩子
        if (isset($addons->hooks) && !empty($addons->hooks)) {
            if (!HookAddonsModel::addHooks($addons->hooks, $name)) {
                $this->error('安装插件钩子时出现错误，请重新安装');
            }
            cache('hook_addons', null);
        }

        // 执行安装插件sql文件
        $sql_file = realpath(ROOT_PATH.'addons/' . $name . '/install.sql');
        if (file_exists($sql_file)) {
            if (isset($addons->database_prefix) && $addons->database_prefix != '') {
                $sql_statement = Sql::getSqlFromFile($sql_file, false, [$addons->database_prefix => config('database.prefix')]);
            } else {
                $sql_statement = Sql::getSqlFromFile($sql_file);
            }

            if (!empty($sql_statement)) {
                foreach ($sql_statement as $value) {
                    Db::execute($value);
                }
            }
        }

        // 插件配置信息
        $addons_info = $addons->info;

        // 验证插件信息
        $result = $this->validate($addons_info, 'Addons');
        // 验证失败 输出错误信息
        if (true !== $result) $this->error($result);

        // 并入插件配置值
        $addons_info['config'] = $addons->getConfigValue();

        // 将插件信息写入数据库
        if (AddonsModel::create($addons_info)) {
            cache('addons_all', null);
            // 记录行为
            action_log('admin_addons_install', 'admin_addons', 0, UID, $addons_name);
            $this->success('插件安装成功');
        } else {
            $this->error('插件安装失败');
        }
    }

    /**
     * 卸载插件
     * @param string $name 插件标识
     * @throws \think\Exception
     * @throws \think\exception\PDOException
     * @author 似水星辰 [ 2630481389@qq.com ]
     */
    public function uninstall($name = '')
    {
        $addons_name = trim($name);
        if ($addons_name == '') $this->error('插件不存在！');

        $class = get_addons_class($addons_name);
        if (!class_exists($class)) {
            $this->error('插件不存在！');
        }

        // 实例化插件
        $addons = new $class;
        // 插件预卸
        if (!$addons->uninstall()) {
            $this->error('插件预卸载失败!原因：' . $addons->getError());
        }

        // 卸载插件自带钩子
        if (isset($addons->hooks) && !empty($addons->hooks)) {
            if (false === HookAddonsModel::deleteHooks($addons_name)) {
                $this->error('卸载插件钩子时出现错误，请重新卸载');
            }
            cache('hook_addons', null);
        }

        // 执行卸载插件sql文件
        $sql_file = realpath(ROOT_PATH.'addons/' . $addons_name . '/uninstall.sql');
        if (file_exists($sql_file)) {
            if (isset($addons->database_prefix) && $addons->database_prefix != '') {
                $sql_statement = Sql::getSqlFromFile($sql_file, true, [$addons->database_prefix => config('database.prefix')]);
            } else {
                $sql_statement = Sql::getSqlFromFile($sql_file, true);
            }

            if (!empty($sql_statement)) {
                Db::execute($sql_statement);
            }
        }

        // 删除插件信息
        if (AddonsModel::where('name', $addons_name)->delete()) {
            cache('addons_all', null);
            // 记录行为
            action_log('admin_addons_uninstall', 'admin_addons', 0, UID, $addons_name);
            $this->success('插件卸载成功');
        } else {
            $this->error('插件卸载失败');
        }
    }

    /**
     * 插件参数设置
     * @param string $name 插件名称
     * @return mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @author 似水星辰 [ 2630481389@qq.com ]
     * @throws \think\Exception
     */
    public function config($name = '')
    {
        // 更新配置
        if ($this->request->isPost()) {
            $data = $this->request->post();
			unset($data['__token__']);
            $data = json_encode($data);
			
            if (false !== AddonsModel::where('name', $name)->update(['config' => $data])) {
                // 记录行为
                action_log('admin_addons_config', 'admin_addons', 0, UID, $name);
                $this->success('更新成功', 'index');
            } else {
                $this->error('更新失败');
            }
        }

        $addons_class = get_addons_class($name);
        // 实例化插件
        $addons = new $addons_class;
        $trigger = isset($addons->trigger) ? $addons->trigger : [];

        // 插件配置值
        $info = AddonsModel::where('name', $name)->field('id,name,config')->find();
        $db_config = json_decode($info['config'], true);

        // 插件配置项
        $config = include ROOT_PATH.'addons/' . $name . '/config.php';

		if(empty($db_config)){
			$this->assign('form_items', $config);
		}else{
			$this->assign('form_items', $this->setData($config, $db_config));
		}
		$this->assign('page_title', '参数设置');
        return $this->fetch('public/edit');

    }

    /**
     * 插件管理
     * @param string $name 插件名
     * @author 似水星辰 [ 2630481389@qq.com ]
     * @return mixed
     */
    public function manage($name = '')
    {
        cookie('__forward__', $_SERVER['REQUEST_URI']);

        // 加载自定义后台页面
        if (addons_action_exists($name, 'Admin', 'index')) {
            return addons_action($name, 'Admin', 'index');
        }

        // 加载系统的后台页面
        $class = get_addons_class($name);
        if (!class_exists($class)) {
            $this->error($name.'插件不存在！');
        }

        // 实例化插件
        $plugin = new $class;

        // 获取后台字段信息，并分析
        if (isset($plugin->admin)) {
            $admin = $this->parseAdmin($plugin->admin);
        } else {
            $admin = $this->parseAdmin();
        }

        if (!addons_model_exists($name)) {
            $this->error('插件: '.$name.' 缺少模型文件！');
        }

        // 获取插件模型实例
        $PluginModel = get_addons_model($name);

        $data_list = $PluginModel->paginate();

        return Format::ins() //实例化
        ->addColumns($admin['columns'])//设置字段
        ->setTopButton(['title'=>'返回插件列表', 'href'=>['index'], 'icon'=>'fa fa-reply pr5', 'class'=>'btn btn-sm mr5 btn-default btn-flat'])
        ->setTopButton(['title'=>'新增', 'href'=>['add',['name' => $name]], 'icon'=>'fa fa-plus pr5', 'class'=>'btn btn-sm mr5 btn-default btn-flat'])
        ->setRightButtons($admin['right_buttons']) // 批量添加右侧按钮
        ->setData($data_list)//设置数据
        ->fetch();//显示
    }

    /**
     * 插件新增方法
     * @param string $name 插件名称
     * @author 似水星辰 [2630481389@qq.com]
     * @return mixed
     */
    public function add($name = '')
    {
        // 如果存在自定义的新增方法，则优先执行
        if (addons_action_exists($name, 'Admin', 'add')) {
            $params = $this->request->param();
            return addons_action($name, 'Admin', 'add', $params);
        }

        // 保存数据
        if ($this->request->isPost()) {
            $data = $this->request->post();

            // 执行插件的验证器（如果存在的话）
            if (addons_validate_exists($name)) {
                $plugin_validate = get_addons_validate($name);
                if (!$plugin_validate->check($data)) {
                    // 验证失败 输出错误信息
                    $this->error($plugin_validate->getError());
                }
            }

            // 实例化模型并添加数据
            $PluginModel = get_addons_model($name);
            if ($PluginModel->create($data)) {
                $this->success('新增成功', cookie('__forward__'));
            } else {
                $this->error('新增失败');
            }
        }

        // 获取插件模型
        $class = get_addons_class($name);
        if (!class_exists($class)) {
            $this->error('插件不存在！');
        }

        // 实例化插件
        $plugin = new $class;
        if (!isset($plugin->fields)) {
            $this->error('插件新增、编辑字段不存在！');
        }

        $this->assign('page_title','新增');
        $this->assign('form_items',$plugin->fields);
        return $this->fetch('public/add');

    }

    /**
     * 编辑插件方法
     * @param string $id 数据id
     * @param string $plugin_name 插件名称
     * @author 似水星辰 [2630481389@qq.com]
     * @return mixed
     */
    public function edit($id = '', $name = '')
    {
        // 如果存在自定义的编辑方法，则优先执行
        if (addons_action_exists($name, 'Admin', 'edit')) {
            $params = $this->request->param();
            return addons_action($name, 'Admin', 'edit', $params);
        }

        // 保存数据
        if ($this->request->isPost()) {
            $data = $this->request->post();

            // 执行插件的验证器（如果存在的话）
            if (addons_action_exists($name)) {
                $plugin_validate = get_addons_validate($name);
                if (!$plugin_validate->check($data)) {
                    // 验证失败 输出错误信息
                    $this->error($plugin_validate->getError());
                }
            }

            // 实例化模型并添加数据
            $PluginModel = get_addons_model($name);
            if (false !== $PluginModel->isUpdate(true)->save($data)) {
                $this->success('编辑成功', cookie('__forward__'));
            } else {
                $this->error('编辑失败');
            }
        }

        // 获取插件类名
        $class = get_addons_class($name);
        if (!class_exists($class)) {
            $this->error('插件不存在！');
        }

        // 实例化插件
        $plugin = new $class;
        if (!isset($plugin->fields)) {
            $this->error('插件新增、编辑字段不存在！');
        }

        // 获取数据
        $PluginModel = get_addons_model($name);
        $info = $PluginModel->find($id);
        if (!$info) {
            $this->error('找不到数据！');
        }

        $this->assign('page_title','编辑');
        $this->assign('form_items', $this->setData($plugin->fields, $info));
        return $this->fetch('public/edit');
    }


    /**
     * 设置状态
     * @param string $type 状态类型:enable/disable
     * @author 似水星辰 [ 2630481389@qq.com ]
     * @return mixed|void
     */
    public function setStatus($type = '')
    {
        $ids = $this->request->isPost() ? input('post.ids/a') : input('param.ids');
        empty($ids) && $this->error('缺少主键');

        $status = $type == 'enable' ? 1 : 0;

        $addonss = AddonsModel::where('id', 'in', $ids)->value('name');
        if ($addonss) {
            HookAddonsModel::$type($addonss);
        }

        if (false !== AddonsModel::where('id', 'in', $ids)->setField('status', $status)) {
            // 记录日志
            call_user_func_array('action_log', ['admin_addons_' . $type, 'admin_addons', 0, UID, $addonss]);
            $this->success('操作成功');
        } else {
            $this->error('操作失败');
        }
    }

    /**
     * 禁用插件
     * @author 似水星辰 [ 2630481389@qq.com ]
     */
    public function disable()
    {
        $this->setStatus('disable');
    }

    /**
     * 启用插件
     * @author 似水星辰 [ 2630481389@qq.com ]
     */
    public function enable()
    {
        $this->setStatus('enable');
    }

    /**
     * 删除插件数据
     * @author 似水星辰 [ 2630481389@qq.com ]
     */
    public function delete($name = '')
    {
        $ids = $this->request->isPost() ? input('post.ids/a') : input('param.ids');
        empty($ids) && $this->error('缺少主键');

        // 获取插件类名
        $class = get_addons_class($name);
        if (!class_exists($class)) {
            $this->error('插件不存在！');
        }
        // 实例化模型并添加数据
        $PluginModel = get_addons_model($name);
        if (false !== $PluginModel::where('id','in',$ids)->delete()){
            $this->success('操作成功');
        } else {
            $this->error('操作失败');
        }
    }


    /**
     * 分析后台字段信息
     * @param array $data 字段信息
     * @author 似水星辰 [2630481389@qq.com]
     * @return array
     */
    private function parseAdmin($data = [])
    {
        $admin = [
            'title'         => '数据列表',
            'search_title'  => '',
            'search_field'  => [],
            'order'         => '',
            'filter'        => '',
            'table_name'    => '',
            'columns'       => [],
            'right_buttons' => [],
            'top_buttons'   => [],
            'customs'       => [],
        ];

        if (empty($data)) {
            return $admin;
        }

        // 处理工具栏按钮链接
        if (isset($data['top_buttons']) && !empty($data['top_buttons'])) {
            $this->parseButton('top_buttons', $data);
        }

        // 处理右侧按钮链接
        if (isset($data['right_buttons']) && !empty($data['right_buttons'])) {
            $this->parseButton('right_buttons', $data);
        }

        return array_merge($admin, $data);
    }

    /**
     * 解析按钮链接
     * @param string $button 按钮名称
     * @param array $data 字段信息
     * @author 似水星辰 [2630481389@qq.com]
     * @return array
     */
    private function parseButton($button = '', &$data)
    {
        foreach ($data[$button] as $key => &$value) {
            // 处理自定义按钮
            if ($key === 'customs') {
                if (!empty($value)) {
                    foreach ($value as &$custom) {
                        if (isset($custom['href']['url']) && $custom['href']['url'] != '') {
                            $params            = isset($custom['href']['params']) ? $custom['href']['params'] : [];
                            $custom['href']    = plugin_url($custom['href']['url'], $params);
                            $data['custom_'.$button][] = $custom;
                        }
                    }
                }
                unset($data[$button][$key]);
            }
            if (!is_numeric($key) && isset($value['href']['url']) && $value['href']['url'] != '') {
                $value['href'] = plugin_url($value['href']['url']);
            }
        }
    }
}
