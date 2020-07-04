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

namespace app\operation\model;

use think\Model as ThinkModel;

/**
 * 单页模型
 * @package app\operation\model
 */
class Servicewords extends ThinkModel
{
    // 设置当前模型对应的完整数据表名称
    protected $table = '__OPERATION_SERVICE_WORDS__';

    // 自动写入时间戳
    protected $autoWriteTimestamp = true;

}