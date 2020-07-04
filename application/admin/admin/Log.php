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

use app\admin\model\Log as LogModel;
use app\common\model\Log as logSysModel;
use service\Format;
use think\facade\Env;

/**
 * 行为日志控制器
 * @package app\admin\controller
 */
class Log extends Base
{

    /**
     * 日志列表
     * @author 似水星辰 [ 2630481389@qq.com ]
     */
    public function index()
    {
        // 数据列表
        $data_list = LogModel::getAll($map, 'admin_log.id desc');

        $fields = [
            ['title', '行为名称'],
            ['username', '执行者'],
            ['action_ip', '执行IP', 'callback', 'long2ip'],
            ['module_title', '所属模块'],
            ['create_time', '执行时间'],
            ['right_button', '操作', 'btn']
        ];
        return Format::ins()//实例化
        ->setPageTitle('行为日志')
            ->hideCheckbox()
            ->setTopButton(['title' => '清空日志', 'href' => 'clear', 'icon' => 'fa fa-times pr5', 'class' => 'btn btn-sm mr5 btn-danger btn-flat ajax-get confirm'])
            ->setRightButton(['title' => '查看详情', 'href' => 'details', 'icon' => 'fa fa-columns pr5', 'class' => 'btn btn-xs mr5 btn-default btn-flat'])
            ->addColumns($fields)//设置字段
            ->setData($data_list)//设置数据
            ->fetch();//显示
    }

    /**
     * 日志详情
     * @param null $id 日志id
     * @return mixed
     * @author 似水星辰 [ 2630481389@qq.com ]
     */
    public function details($id = null)
    {
        if ($id === null) $this->error('缺少参数');
        $info = LogModel::getAll(['admin_log.id' => $id]);
        $info = $info[0];
        $info['action_ip'] = long2ip($info['action_ip']);
        $fields = [
            ['type' => 'static', 'name' => 'title', 'title' => '行为名称'],
            ['type' => 'static', 'name' => 'username', 'title' => '执行者'],
            ['type' => 'static', 'name' => 'record_id', 'title' => '目标ID'],
            ['type' => 'static', 'name' => 'action_ip', 'title' => '执行IP'],
            ['type' => 'static', 'name' => 'module_title', 'title' => '所属模块'],
            ['type' => 'static', 'name' => 'remark', 'title' => '备注']
        ];

        $this->assign('page_title', '行为日志');
        $this->assign('btn_hide', 1);
        $this->assign('form_items', $this->setData($fields, $info));
        return $this->fetch('public/edit');
    }

    /**
     * 清空日志
     * @author 似水星辰 [ 2630481389@qq.com ]
     */
    public function clear()
    {
        $res = logModel::destroy(['status' => 1]);
        if ($res) {
            $this->success('清空日志成功');
        } else {
            $this->error('清空日志失败');
        }
    }

    /**
     * 获取系统日志
     * @author 似水星辰 [ 2630481389@qq.com ]
     * @created 2019/10/12 8:33
     */
    public function get_system_log()
    {
        $logSysModel = new logSysModel();
        if (false === $logSysModel->isAllow()) {
            die($logSysModel->getError());
        }
        $directory = $logSysModel->getDirectory();
        $file_paths = input('param.file_paths');
        $filePaths = isset($file_paths) ? $file_paths : '/'.date('Ym').'/'.date('d').'.log';
        if (mb_strpos($filePaths, '_cli.log') !== false) {
            $path = $logSysModel->complementLogPath($filePaths);
            if (false === $path) {
                $this->error(404);
            }
            $content = file_get_contents($path);
            $this->success('', '', $content);
        }
        $rows = $logSysModel->getLogs($filePaths);
        $info = $logSysModel->getInfo($filePaths);
        if (false === $rows) {
            $this->error('获取失败');
        }

        $this->assign(compact('rows'));
        $this->assign(compact('info'));
        $this->assign(compact('directory'));
        $this->assign(compact('file_paths'));
        return $this->fetch();
    }

    /**
     * 删除对应日志文件
     * @param string $ids
     * @author 似水星辰 [ 2630481389@qq.com ]
     * @created 2019/10/12 11:43
     */
    public function del()
    {
        $filePaths = input('param.file_paths');
        $file = Env::get('runtime_path') . 'log'.$filePaths;
        if (file_exists($file)) {
            if(unlink($file)){
                $this->success('删除成功');
            }
        }
        $this->error('删除失败');
    }
}