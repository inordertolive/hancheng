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

namespace addons\DySms\model;

use app\common\model\Addons;

/**
 * 后台插件模型
 * @package plugins\DySms\model
 * @author 小乌 <82950492@qq.com>
 */
class DySms extends Addons
{
    // 设置当前模型对应的完整数据表名称
    protected $name = 'addons_dysms';

    /**
     * 获取模板数据
     * @param string $title 模板名称
     * @return array|false|\PDOStatement|string|\think\Model
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public static function getTemplate($title = '')
    {
        return self::where('title', $title)->find();
    }
}