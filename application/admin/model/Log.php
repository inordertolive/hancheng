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

namespace app\admin\model;

use think\Model as ThinkModel;

/**
 * 日志记录模型
 * @package app\admin\model
 */
class Log extends ThinkModel
{
    // 设置当前模型对应的完整数据表名称
    protected $table = '__ADMIN_LOG__';

    // 自动写入时间戳
    protected $autoWriteTimestamp = true;

    /**
     * 获取所有日志
     * @param array $map 条件
     * @param string $order 排序
     * @author 似水星辰 [ 2630481389@qq.com ]
     * @return mixed
     * @throws \think\exception\DbException
     */
    public static function getAll($map = [], $order = '')
    {
        $data_list = self::view('admin_log', true)
            ->view('admin_action', 'title,module', 'admin_action.id=admin_log.action_id', 'left')
            ->view('admin', 'username', 'admin.id=admin_log.user_id', 'left')
            ->view('module', ['title' => 'module_title'], 'module.name=admin_action.module')
            ->where($map)
            ->order($order)
            ->paginate();
        return $data_list;
    }
}