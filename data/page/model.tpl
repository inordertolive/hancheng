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

namespace app\{module}\model;

use think\Model as ThinkModel;

/**
 * 单页模型
 * @package app\{module}\model
 */
class {model} extends ThinkModel
{
    // 设置当前模型对应的完整数据表名称
    protected $table = '__{table}__';

    // 设置主键
    protected $pk = 'aid';

    // 自动写入时间戳
    protected $autoWriteTimestamp = true;

}