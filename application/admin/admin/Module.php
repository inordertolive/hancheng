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

use app\admin\model\Module as ModuleModel;
use app\admin\model\Addons as PluginModel;
use app\admin\model\Menu as MenuModel;
use app\admin\model\Action as ActionModel;
use service\Sql;
use service\File;
use service\Tree;
use service\Database;
use service\PHPZip;
use think\Db;

/**
 * 模块管理控制器
 * @package app\admin\admin
 */
class Module extends Base
{
    /**
     * 模块首页
     * @param string $group 分组
     * @author 似水星辰 [ 2630481389@qq.com ]
     * @return mixed|string
     */
    public function index($group = 'local')
    {
        // 配置分组信息
        $list_group = ['local' => '本地模块'];
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

                $ModuleModel = new ModuleModel();
                $result = $ModuleModel->getAll($keyword, $status);

                if ($result['modules'] === false) {
                    $this->error($ModuleModel->getError());
                }

                $this->assign('page_title', '模块管理');
                $this->assign('modules', $result['modules']);
                $this->assign('total', $result['total']);
                $this->assign('tab_nav', ['tab_list' => $tab_list, 'active' => $group]);
                return $this->fetch();
                break;
            case 'online':
                return '<h2>正在建设中...</h2>';
                break;
            default:
                $this->error('非法操作');
        }
    }

    /**
     * 安装模块
     * @param string $name 模块标识
     * @param int $confirm 是否确认
     * @throws \think\Exception
     * @throws \think\exception\PDOException
     * @author 似水星辰 [ 2630481389@qq.com ]
     */
    public function install($name = '', $confirm = 0)
    {
        // 设置最大执行时间和内存大小
        ini_set('max_execution_time', '0');
        ini_set('memory_limit', '1024M');

        if ($name == '') $this->error('模块不存在！');
        if ($name == 'admin') $this->error('禁止操作系统核心模块！');

        // 模块配置信息
        $module_info = ModuleModel::getInfoFromFile($name);

        if ($confirm == 0) {
            $need_module = [];
            $need_plugin = [];
            $table_check = [];
            // 检查模块依赖
            if (isset($module_info['need_module']) && !empty($module_info['need_module'])) {
                $need_module = $this->checkDependence('module', $module_info['need_module']);
            }

            // 检查插件依赖
            if (isset($module_info['need_plugin']) && !empty($module_info['need_plugin'])) {
                $need_plugin = $this->checkDependence('plugin', $module_info['need_plugin']);
            }

            // 检查数据表
            if (isset($module_info['tables']) && !empty($module_info['tables'])) {
                foreach ($module_info['tables'] as $table) {
                    if (Db::query("SHOW TABLES LIKE '" . config('database.prefix') . "{$table}'")) {
                        $table_check[] = [
                            'table' => config('database.prefix') . "{$table}",
                            'result' => '<small class="lable bg-red pd5">已存在</small>'
                        ];
                    } else {
                        $table_check[] = [
                            'table' => config('database.prefix') . "{$table}",
                            'result' => '<small class="lable bg-green pd5">可安装</small>'
                        ];
                    }
                }
            }

            $this->assign('need_module', $need_module);
            $this->assign('need_plugin', $need_plugin);
            $this->assign('table_check', $table_check);
            $this->assign('name', $name);
            $this->assign('page_title', '安装模块：' . $name);
            return $this->fetch();
        }

        // 执行安装文件
        $install_file = realpath(APP_PATH . $name . '/install.php');
        if (file_exists($install_file)) {
            @include($install_file);
        }

        // 执行安装模块sql文件
        $clear = $this->request->post('clear');
        $sql_file = realpath(APP_PATH . $name . '/sql/install.sql');
        if (file_exists($sql_file) &&  $clear==1) {
            if (isset($module_info['database_prefix']) && !empty($module_info['database_prefix'])) {
                $sql_statement = Sql::getSqlFromFile($sql_file, false, [$module_info['database_prefix'] => config('database.prefix')]);
            } else {
                $sql_statement = Sql::getSqlFromFile($sql_file);
            }

            if (!empty($sql_statement)) {
                foreach ($sql_statement as $value) {
                    try {
                        Db::execute($value);
                    } catch (\Exception $e) {
                        $this->error('导入SQL失败，请检查install.sql的语句是否正确');
                    }
                }
            }
        }

        // 添加菜单
        $menus = ModuleModel::getMenusFromFile($name);
        if (is_array($menus) && !empty($menus)) {
            if (false === $this->addMenus($menus, $name)) {
                $this->error('菜单添加失败，请重新安装');
            }
        }

        // 检查是否有模块设置信息
        if (isset($module_info['config']) && !empty($module_info['config'])) {
            $module_info['config'] = json_encode(parse_config($module_info['config']));
        }

        // 检查是否有模块授权配置
        if (isset($module_info['access']) && !empty($module_info['access'])) {
            $module_info['access'] = json_encode($module_info['access']);
        }

        // 检查是否有行为规则
        if (isset($module_info['action']) && !empty($module_info['action'])) {
            $ActionModel = new ActionModel;
            if (!$ActionModel->saveAll($module_info['action'])) {
                MenuModel::where('module', $name)->delete();
                $this->error('行为添加失败，请重新安装');
            }
        }

        // 将模块信息写入数据库
        $ModuleModel = new ModuleModel($module_info);
        $allowField = ['name', 'title', 'icon', 'description', 'author', 'author_url', 'config', 'access', 'version', 'identifier', 'status'];

        if ($ModuleModel->allowField($allowField)->save()) {
            // 复制静态资源目录
            File::copy_dir(APP_PATH . $name . '/public', ROOT_PATH . 'public/theme/'. $name);
            // 删除静态资源目录
            File::del_dir(APP_PATH . $name . '/public');
            cache('modules', null);
            cache('module_all', null);
            // 记录行为
            action_log('admin_module_install', 'admin_module', 0, UID, $module_info['title']);
            $this->success('模块安装成功', 'index');
        } else {
            MenuModel::where('module', $name)->delete();
            $this->error('模块安装失败');
        }
    }

    /**
     * 卸载模块
     * @param string $name 模块名
     * @param int $confirm 是否确认
     * @return mixed
     * @throws \think\Exception
     * @throws \think\exception\PDOException
     * @author 似水星辰 [ 2630481389@qq.com ]
     */
    public function uninstall($name = '', $confirm = 0)
    {
        if ($name == '') $this->error('模块不存在！');
        if ($name == 'admin') $this->error('禁止操作系统模块！');

        // 模块配置信息
        $module_info = ModuleModel::getInfoFromFile($name);

        if ($confirm == 0) {
            $this->assign('name', $name);
            $this->assign('page_title', '卸载模块：' . $name);
            return $this->fetch();
        }

        // 执行卸载文件
        $uninstall_file = realpath(APP_PATH . $name . '/uninstall.php');
        if (file_exists($uninstall_file)) {
            @include($uninstall_file);
        }

        // 执行卸载模块sql文件
        $clear = $this->request->post('clear');

        if ($clear == 1) {
            $sql_file = realpath(APP_PATH . $name . '/sql/uninstall.sql');
            if (file_exists($sql_file)) {
                if (isset($module_info['database_prefix']) && !empty($module_info['database_prefix'])) {
                    $sql_statement = Sql::getSqlFromFile($sql_file, false, [$module_info['database_prefix'] => config('database.prefix')]);
                } else {
                    $sql_statement = Sql::getSqlFromFile($sql_file);
                }

                if (!empty($sql_statement)) {
                    foreach ($sql_statement as $sql) {
                        try {
                            Db::execute($sql);
                        } catch (\Exception $e) {
                            $this->error('卸载失败，请检查uninstall.sql的语句是否正确');
                        }
                    }
                }
            }
        }

        // 删除菜单
        if (false === MenuModel::where('module', $name)->delete()) {
            $this->error('菜单删除失败，请重新卸载');
        }

        // 删除授权信息
        if (false === Db::name('admin_access')->where('module', $name)->delete()) {
            $this->error('删除授权信息失败，请重新卸载');
        }

        // 删除行为规则
        if (false === Db::name('admin_action')->where('module', $name)->delete()) {
            $this->error('删除行为信息失败，请重新卸载');
        }

        // 删除模块信息
        if (ModuleModel::where('name', $name)->delete()) {
            // 复制静态资源目录
            File::copy_dir(ROOT_PATH . 'public/static/' . $name, APP_PATH . $name . '/public/static/' . $name);
            // 删除静态资源目录
            File::del_dir(ROOT_PATH . 'public/static/' . $name);
            cache('modules', null);
            cache('module_all', null);
            // 记录行为
            action_log('admin_module_uninstall', 'admin_module', 0, UID, $module_info['title']);
            $this->success('模块卸载成功', 'index');
        } else {
            $this->error('模块卸载失败');
        }
    }

    /**
     * 更新模块配置
     * @param string $name 模块名
     * @author 似水星辰 [ 2630481389@qq.com ]
     */
    public function update($name = '')
    {
        $name == '' && $this->error('缺少模块名！');

        $Module = ModuleModel::get(['name' => $name]);
        !$Module && $this->error('模块不存在，或未安装');

        // 模块配置信息
        $module_info = ModuleModel::getInfoFromFile($name);
        unset($module_info['name']);

        // 检查是否有模块设置信息
        if (isset($module_info['config']) && !empty($module_info['config'])) {
            $module_info['config'] = json_encode(parse_config($module_info['config']));
        } else {
            $module_info['config'] = '';
        }

        // 检查是否有模块授权配置
        if (isset($module_info['access']) && !empty($module_info['access'])) {
            $module_info['access'] = json_encode($module_info['access']);
        } else {
            $module_info['access'] = '';
        }

        // 更新模块信息
        if (false !== $Module->save($module_info)) {
            $this->success('模块配置更新成功');
        } else {
            $this->error('模块配置更新失败，请重试');
        }
    }


    /**
     * 导出模块
     * @param string $name 模块名
     * @author 似水星辰 [ 2630481389@qq.com ]
     * @return mixed
     */
    public function export($name = '')
    {
        if ($name == '') $this->error('缺少模块名');

        $export_data = $this->request->get('export_data', '');
        if ($export_data == '') {
            $this->assign('page_title', '导出模块：'. $name);
            return $this->fetch();
        }

        // 模块导出目录
        $module_dir = ROOT_PATH. 'export/module/'. $name;

        // 删除旧的导出数据
        if (is_dir($module_dir)) {
            File::del_dir($module_dir);
        }

        // 复制模块目录到导出目录
        File::copy_dir(APP_PATH. $name, $module_dir);
        // 复制静态资源目录
        File::copy_dir(ROOT_PATH. 'public/static/'. $name, $module_dir.'/public/static/'. $name);

        // 模块本地配置信息
        $module_info = ModuleModel::getInfoFromFile($name);

        // 检查是否有模块设置信息
        if (isset($module_info['config'])) {
            $db_config = ModuleModel::where('name', $name)->value('config');
            $db_config = json_decode($db_config, true);
            // 获取最新的模块设置信息
            $module_info['config'] = set_config_value($module_info['config'], $db_config);
        }

        // 检查是否有模块行为信息
        $action = Db::name('admin_action')->where('module', $name)->field('module,name,title,remark,rule,log,status')->select();
        if ($action) {
            $module_info['action'] = $action;
        }

        // 表前缀
        $module_info['database_prefix'] = config('database.prefix');

        // 生成配置文件
        if (false === $this->buildInfoFile($module_info, $name)) {
            $this->error('模块配置文件创建失败，请重新导出');
        }

        // 获取模型菜单并导出
        $fields = 'id,pid,title,icon,url_value,url_target,online_hide,sort,status';
        $menus = MenuModel::getMenusByGroup($name, $fields);
        if (false === $this->buildMenuFile($menus, $name)) {
            $this->error('模型菜单文件创建失败，请重新导出');
        }

        // 导出数据库表
        if (isset($module_info['tables']) && !empty($module_info['tables'])) {
            if (!is_dir($module_dir. '/sql')) {
                mkdir($module_dir. '/sql', 644, true);
            }
            if (!Database::export($module_info['tables'], $module_dir. '/sql/install.sql', config('database.prefix'), $export_data)) {
                $this->error('数据库文件创建失败，请重新导出');
            }
            if (!Database::exportUninstall($module_info['tables'], $module_dir. '/sql/uninstall.sql', config('database.prefix'))) {
                $this->error('数据库文件创建失败，请重新导出');
            }
        }

        if(!$this->exportApiSql($name, $module_dir. '/sql/install.sql')){
            $this->error('API接口导出失败，请重新导出');
        }

        if(!$this->exportUnApiSql($name, $module_dir. '/sql/uninstall.sql')){
            $this->error('API接口导出失败，请重新导出');
        }

        // 记录行为
        action_log('module_export', 'admin_module', 0, UID, $module_info['title']);

        // 打包下载
        $archive = new PHPZip;
        return $archive->ZipAndDownload($module_dir, $name);
    }

    /**
     * 获取接口安装SQL
     * @author 似水星辰 [ 2630481389@qq.com ]
     * @created 2019/11/1 16:00
     */
    private function exportApiSql($name, $dir){
        $sql = file_get_contents($dir);
        $prefix = config('database.prefix');
        $sql .= "\n-- -----------------------------\n";
        $sql .= "-- 接口列表 --\n";
        $sql .= "-- -----------------------------\n";
        // 导出接口记录
        $result = Db::query("SELECT apiName,hash,`module`,checkSign,needLogin,status,method,info,isTest,returnStr,create_time,`group`,readme FROM ".$prefix."admin_api_list where module = '".$name."' LIMIT 0, 1000");
        foreach ($result as $key => $row) {
            if($key == 0){
                $hash = "'".$row['hash']."'";
            }else{
                $hash .= ",'".$row['hash']."'";
            }
            $row = array_map('addslashes', $row);
            $sql .= "INSERT INTO ".$prefix."admin_api_list (apiName,hash,`module`,checkSign,needLogin,status,method,info,isTest,returnStr,create_time,`group`,readme) VALUES ('" . str_replace(array("\r","\n"),array('\r','\n'),implode("', '", $row)) . "');\n";
        }

        $sql .= "\n-- -----------------------------\n";
        $sql .= "-- 接口字段列表 --\n";
        $sql .= "-- -----------------------------\n";

        // 导出接口字段记录
        $result2 = Db::query("SELECT fieldName,hash,dataType,`default`,isMust,`range`,info,`type`,showName,pid,sort FROM ".$prefix."admin_api_fields where hash in (".$hash.") LIMIT 0, 1000");
        foreach($result2 as $row2){
            $row2 = array_map('addslashes', $row2);
            $sql .= "INSERT INTO ".$prefix."admin_api_fields (fieldName,hash,dataType,`default`,isMust,`range`,info,`type`,showName,pid,sort) VALUES ('" . str_replace(array("\r","\n"),array('\r','\n'),implode("', '", $row2)) . "');\n";
        }
        // 写入文件
        if (file_put_contents($dir, $sql)) {
            return true;
        };
    }

    /**
     * 获取接口卸载SQL
     * @param $dir
     * @author 似水星辰 [ 2630481389@qq.com ]
     * @created 2019/11/1 16:05
     */
    private function exportUnApiSql($name, $dir){
        $sql = file_get_contents($dir);
        $prefix = config('database.prefix');
        // 备份数据记录
        $result = Db::query("SELECT hash FROM ".$prefix."admin_api_list where module = '".$name."' LIMIT 0, 1000");
        foreach ($result as $key => $row) {
			$sql .= "DELETE FROM `".$prefix."admin_api_list` WHERE `hash` ='".$row['hash']."';\n";
			$sql .= "DELETE FROM `".$prefix."admin_api_fields` WHERE `hash` ='".$row['hash']."';\n";
        }

        // 写入文件
        if (file_put_contents($dir, $sql)) {
            return true;
        };
    }

    /**
     * 创建模块菜单文件
     * @param array $menus 菜单
     * @param string $name 模块名
     * @author 似水星辰 [ 2630481389@qq.com ]
     * @return int
     */
    private function buildMenuFile($menus = [], $name = '')
    {
        $menus = Tree::toLayer($menus);

        // 美化数组格式
        $menus = var_export($menus, true);
        $menus = preg_replace("/(\d+|'id'|'pid') =>(.*)/", '', $menus);
        $menus = preg_replace("/'child' => (.*)(\r\n|\r|\n)\s*array/", "'child' => $1array", $menus);
        $menus = str_replace(['array (', ')'], ['[', ']'], $menus);
        $menus = preg_replace("/(\s*?\r?\n\s*?)+/", "\n", $menus);

        $content = <<<INFO
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

/**
 * 菜单信息
 */
return {$menus};

INFO;
        // 写入到文件
        return file_put_contents(ROOT_PATH. 'export/module/'. $name. '/menus.php', $content);
    }

    /**
     * 创建模块配置文件
     * @param array $info 模块配置信息
     * @param string $name 模块名
     * @author 似水星辰 [ 2630481389@qq.com ]
     * @return int
     */
    private function buildInfoFile($info = [], $name = '')
    {
        // 美化数组格式
        $info = var_export($info, true);
        $info = preg_replace("/'(.*)' => (.*)(\r\n|\r|\n)\s*array/", "'$1' => array", $info);
        $info = preg_replace("/(\d+) => (\s*)(\r\n|\r|\n)\s*array/", "array", $info);
        $info = preg_replace("/(\d+ => )/", "", $info);
        $info = preg_replace("/array \((\r\n|\r|\n)\s*\)/", "[)", $info);
        $info = preg_replace("/array \(/", "[", $info);
        $info = preg_replace("/\)/", "]", $info);

        $content = <<<INFO
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

/**
 * 模块信息
 */
return {$info};

INFO;
        // 写入到文件
        return file_put_contents(ROOT_PATH. 'export/module/'. $name. '/info.php', $content);
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
    public function setStatus($type = '', $record = [])
    {
        $ids = input('param.ids');
        empty($ids) && $this->error('缺少主键');

        $module = ModuleModel::where('id', $ids)->find();
        $module['system_module'] == 1 && $this->error('禁止操作系统内置模块');

        $status = $type == 'enable' ? 1 : 0;

        // 将模块对应的菜单禁用或启用
        $map = [
            'pid' => 0,
            'module' => $module['name']
        ];
        MenuModel::where($map)->setField('status', $status);

        if (false !== ModuleModel::where('id', $ids)->setField('status', $status)) {
            // 记录日志
            call_user_func_array('action_log', ['admin_module_' . $type, 'admin_module', 0, UID, $module['title']]);
            $this->success('操作成功');
        } else {
            $this->error('操作失败');
        }
    }

    /**
     * 禁用模块
     * @param array $record 行为日志内容
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @author 似水星辰 [ 2630481389@qq.com ]
     */
    public function disable($record = [])
    {
        $this->setStatus('disable');
    }

    /**
     * 启用模块
     * @param array $record 行为日志内容
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @author 似水星辰 [ 2630481389@qq.com ]
     */
    public function enable($record = [])
    {
        $this->setStatus('enable');
    }

    /**
     * 添加模型菜单
     * @param array $menus 菜单
     * @param string $module 模型名称
     * @param int $pid 父级ID
     * @author 似水星辰 [ 2630481389@qq.com ]
     * @return bool
     */
    private function addMenus($menus = [], $module = '', $pid = 0)
    {
        foreach ($menus as $menu) {
            $data = [
                'pid' => $pid,
                'module' => $module,
                'title' => $menu['title'],
                'icon' => isset($menu['icon']) ? $menu['icon'] : 'fa fa-fw fa-puzzle-piece',
                'url_value' => isset($menu['url_value']) ? $menu['url_value'] : '',
                'url_target' => isset($menu['url_target']) ? $menu['url_target'] : '_self',
                'online_hide' => isset($menu['online_hide']) ? $menu['online_hide'] : 0,
                'status' => isset($menu['status']) ? $menu['status'] : 1
            ];

            $result = MenuModel::create($data);
            if (!$result) return false;

            if (isset($menu['child'])) {
                $this->addMenus($menu['child'], $module, $result['id']);
            }
        }

        return true;
    }

    /**
     * 检查依赖
     * @param string $type 类型：module/plugin
     * @param array $data 检查数据
     * @author 似水星辰 [ 2630481389@qq.com ]
     * @return array
     */
    private function checkDependence($type = '', $data = [])
    {
        $need = [];
        foreach ($data as $key => $value) {
            if (!isset($value[3])) {
                $value[3] = '=';
            }
            // 当前版本
            if ($type == 'module') {
                $curr_version = ModuleModel::where('identifier', $value[1])->value('version');
            } else {
                $curr_version = PluginModel::where('identifier', $value[1])->value('version');
            }

            // 比对版本
            $result = version_compare($curr_version, $value[2], $value[3]);
            $need[$key] = [
                $type => $value[0],
                'identifier' => $value[1],
                'version' => $curr_version ? $curr_version : '未安装',
                'version_need' => $value[3] . $value[2],
                'result' => $result ? '<i class="fa fa-check text-success"></i>' : '<i class="fa fa-times text-danger"></i>'
            ];
        }

        return $need;
    }

	
    /**
     * 创建新模块
     * @return void
     * @author 似水星辰 [ 2630481389@qq.com ]
     * @created 2019/11/25 9:30
     * @editor XX [ xxx@qq.com ]
     * @updated 2019.03.30
     */
    public function add()
    {
        // 保存数据
        if ($this->request->isPost()) {
            // 表单数据
            $data = $this->request->post();
            if(ModuleModel::where('name',$data['name'])->count()){
                $this->error('模块名已经存在，请更换');
            }
            $data['identifier'] = $data['name'] . '.zbphp.module';
            $content = File::read_file(ROOT_PATH . 'data/page/info.tpl');
            $content = str_replace(['{name}', '{title}', '{identifier}', '{icon}', '{description}', '{author}', '{version}'], [$data['name'], $data['title'], $data['identifier'], $data['icon'], $data['description'], $data['author'], $data['version']], $content);
            if (!File::mk_dir(APP_PATH . $data['name'])) {
                $this->error('创建模块' . $data['name'] . '目录失败,请检查权限或是否已存在');
            }
            if (!File::mk_dir(APP_PATH . $data['name'] . "/admin")) {
                $this->error('创建admin目录失败,请检查权限或是否已存在');
            }
            if (!File::mk_dir(APP_PATH . $data['name'] . "/controller")) {
                $this->error('创建controller目录失败,请检查权限或是否已存在');
            }
            if (!File::mk_dir(APP_PATH . $data['name'] . "/model")) {
                $this->error('创建model目录失败,请检查权限或是否已存在');
            }
            if (!File::mk_dir(APP_PATH . $data['name'] . "/validate")) {
                $this->error('创建validate目录失败,请检查权限或是否已存在');
            }
            if (!File::mk_dir(APP_PATH . $data['name'] . "/view")) {
                $this->error('创建view目录失败,请检查权限或是否已存在');
            }
            if (!File::mk_dir(APP_PATH . $data['name'] . "/sql")) {
                $this->error('创建sql目录失败,请检查权限或是否已存在');
            }
            if (!File::write_file(APP_PATH . $data['name'] . "/info.php", $content)) {
                $this->error('创建配置文件失败,请检查权限或是否已存在');
            }

            //生成默认菜单文件
            $a_file_dir = APP_PATH . $data['name'] . '/menus.php';
            $a_file_content = File::read_file(ROOT_PATH . 'data/page/menus.tpl');
            $a_file_content = str_replace(['{name}', '{title}', '{icon}'], [$data['name'], $data['title'], $data['icon']], $a_file_content);
            if (!File::write_file($a_file_dir, $a_file_content)) {
                $this->error('创建菜单文件失败,请检查权限或是否已存在');
            }

            //生成默认控制器
            $c_file_dir = APP_PATH . $data['name'] . '/' . 'admin' . '/Index.php';
            $c_file_content = File::read_file(ROOT_PATH . 'data/page/test.tpl');
            $c_file_content = str_replace(['{module}', '{model}', '{title}', '{fields}', '{fields_add}'], [$data['name'], 'Index', $data['title'], '', ''], $c_file_content);
            if (!File::write_file($c_file_dir, $c_file_content)){
                $this->error('创建默认控制器文件失败,请检查权限或是否已存在');
            }

            $this->success('创建模块成功，请先进行安装','index');
        }

        $fields = [
            ['type' => 'text', 'name' => 'name', 'title' => '模块标识', 'tips' => '请使用英文字母，根据标识会生成唯一模块标识', 'attr' => 'data-rule="required;name;" data-rule-name="[/^[a-zA-Z]*$/, \'请输入正确的标识，只能使用英文，请勿使用符号和数字\']" data-msg-required="标识不能为空"'],
            ['type' => 'text', 'name' => 'title', 'title' => '模块名称', 'tips' => '', 'attr' => '', 'value' => '', 'attr' => 'data-rule="required;"'],
            ['type' => 'icon', 'name' => 'icon', 'title' => '图标', 'tips' => '点击后请选择图标'],
            ['type' => 'textarea', 'name' => 'description', 'title' => '简介'],
            ['type' => 'text', 'name' => 'author', 'title' => '作者'],
            ['type' => 'text', 'name' => 'version', 'title' => '版本号', 'tips' => '请填写三段式版本号', 'attr' => '', 'value' => '1.0.0', 'attr' => 'data-rule="required;"'],

        ];
        $this->assign('page_title', '创建模块');
        $this->assign('form_items', $fields);
        return $this->fetch('../../admin/view/public/add');
    }
}