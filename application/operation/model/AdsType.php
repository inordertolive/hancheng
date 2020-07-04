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
 * 广告分类模型
 * @package app\cms\model
 */
class AdsType extends ThinkModel
{
    // 设置当前模型对应的完整数据表名称
    protected $table = '__OPERATION_ADS_TYPE__';

    // 自动写入时间戳
    protected $autoWriteTimestamp = true;
}