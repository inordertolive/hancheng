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

use app\admin\model\Config as ConfigModel;
use app\admin\model\Module as ModuleModel;

/**
 * 系统设置控制器
 * @package app\admin\admin
 */
class System extends Base
{
    /**
     * 系统设置
     * @param string $group 分组
     * @author 似水星辰 [ 2630481389@qq.com ]
     * @return mixed
     */
    public function index($group = 'base')
    {
        // 保存数据
        if ($this->request->isPost()) {
            $data = $this->request->post();
			
            if (isset(config('config_group')[$group])) {
                // 查询该分组下所有的配置项名和类型
                $items = ConfigModel::where('group', $data['group'])->where('status', 1)->column('name,type');

                foreach ($items as $name => $type) {
                    if (!isset($data[$name])) {
                        switch ($type) {
                            case 'checkbox':
                                $data[$name] = '';
                                break;
                        }
                    } else {
                        // 如果值是数组则转换成字符串，适用于复选框等类型
                        if (is_array($data[$name])) {
                            $data[$name] = implode(',', $data[$name]);
                        }
                        switch ($type) {
                            // 日期时间
                            case 'datetime':
                                $data[$name] = strtotime($data[$name]);
                                break;
                        }
                    }
                    ConfigModel::where('name', $name)->update(['value' => $data[$name]]);
                }
            } else {
                unset($data['group'], $data['__token__']);
                // 保存模块配置
                if (false === ModuleModel::where('name', $group)->update(['config' => json_encode($data)])) {
                    $this->error('更新失败');
                }
                // 非开发模式，缓存数据
                if (config('develop_mode') == 0) {
                    cache('module_config_'.$group, $data);
                }
            }
            cache('system_config', null);
            // 记录行为
            action_log('admin_config_update', 'admin_config', 0, UID, "分组($group)");
            $this->success('更新成功', url('index', ['group' => $group]));
        } else {
            // 配置分组信息
            $list_group = config('config_group');

            // 读取模型配置
            $modules = ModuleModel::where('config', 'neq', '')
                ->where('status', 1)
                ->column('config,title,name', 'name');
            foreach ($modules as $name => $module) {
                $list_group[$name] = $module['title'];
            }

            $tab_list   = [];
            foreach ($list_group as $key => $value) {
                $tab_list[$key]['title'] = $value;
                $tab_list[$key]['url']  = url('index', ['group' => $key]);
            }
            if (isset(config('config_group')[$group])) {
                // 查询条件
                $map['group']  = $group;
                $map['status'] = 1;

                // 数据列表
                $data_list = ConfigModel::where($map)
                    ->order('sort asc,id asc')
                    ->column('name,title,type,value,extra,tips');

                foreach ($data_list as &$value) {
                    // 解析extra
                    if ($value['extra'] != '') {
                        $value['extra'] = parse_attr($value['extra']);
                    }
                }

				// 默认模块列表
                if (isset($data_list['home_default_module'])) {
                    $list_module['index'] = '默认';
                    $data_list['home_default_module']['extra'] = array_merge($list_module, ModuleModel::getModule());
                }

				$this->assign('group',$group);
				$this->assign('form_items',$data_list);
				$this->assign('tab_list',$tab_list);
                return $this->fetch();
            } else {
                // 模块配置
                $module_info = ModuleModel::getInfoFromFile($group);
                $config      = $module_info['config'];

                // 数据库内的模块信息
                $db_config = ModuleModel::where('name', $group)->value('config');
                $db_config = json_decode($db_config, true);

				$this->assign('group',$group);
				$this->assign('form_items',$this->setData($config,$db_config));
				$this->assign('tab_list',$tab_list);
                return $this->fetch();
            }
        }
    }
}