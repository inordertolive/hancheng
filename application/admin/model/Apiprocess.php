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
 * 单页模型
 * @package app\admin\model
 */
class Apiprocess extends ThinkModel
{
    // 设置当前模型对应的完整数据表名称
    protected $table = '__ADMIN_API_PROCESS__';

    // 设置主键
    protected $pk = 'aid';

    // 自动写入时间戳
    protected $autoWriteTimestamp = true;

}