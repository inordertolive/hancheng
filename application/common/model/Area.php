<?php
// +----------------------------------------------------------------------
// | LwwanPHP[基于ThinkPHP5.1开发]
// +----------------------------------------------------------------------
// | Copyright (c) 2016-2019 http://www.lwwan.com
// +----------------------------------------------------------------------
// | Author 似水星辰 [ 2630481389@qq.com ]
// +----------------------------------------------------------------------
// | 星辰工作室 QQ群331378225
// +----------------------------------------------------------------------
namespace app\common\model;

use service\Tree;
use think\Model as ThinkModel;
/**
 * 订单商品列表
 * @package app\goods\model
 */
class Area extends  ThinkModel
{

    // 设置当前模型对应的完整数据表名称
    protected $table = '__CHINA_AREA__';

    // 自动写入时间戳
    protected $autoWriteTimestamp = false;

    /**
     * 获取地区缓存
     * @author 似水星辰 [ 2630481389@qq.com ]
     * @created 2019/11/11 10:14
     */
    public  static function get_cache(){
        $area = cache('area_region');
        if(!$area){
            $area = self::column('id,pid,name,level');
            $area = Tree::config(['child' => 'city'])->toLayer($area);
            cache('area_region', $area);
        }
        return $area;
    }
    /**
     * 根据id获取地区名称
     * @param $id
     * @return string
     */
    public static function getNameById($id)
    {
        $region = self::get_cache();
        return $region[$id]['name'];
    }

    /**
     * 根据名称获取地区id
     * @param $name
     * @param int $level
     * @param int $pid
     * @return mixed
     */
    public static function getIdByName($name, $level = 0, $pid = 0)
    {
        return static::useGlobalScope(false)->where(compact('name', 'level', 'pid'))->value('id');
    }

}