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

namespace app\user\model;

use think\Model as ThinkModel;

/**
 * 单页模型
 * @package app\user\model
 */
class Level extends ThinkModel
{
    // 设置当前模型对应的完整数据表名称
    protected $table = '__USER_LEVEL__';

    // 自动写入时间戳
    protected $autoWriteTimestamp = true;

    /**
     * 获取等级名称
     * @param $consumption
     * @author 似水星辰 [ 2630481389@qq.com ]
     * @created 2019/4/27 11:25
     * @return mixed
     */
    public static function getLevelName($consumption)
    {
        $level = self::getLevel($consumption);
        return $level['name'];
    }

    /**
     * 简报
     * @param float $consumption
     * @return int
     */
    public static function getLevelId($consumption)
    {
        $level = self::getLevel($consumption);
        return $level['levelid'];
    }

    /**
     * 获取消费等级
     * @param $consumption
     * @author 似水星辰 [ 2630481389@qq.com ]
     * @created 2019/4/27 11:25
     * @editor XX [ xxx@qq.com ]
     * @updated 2019.03.30
     * @return array|mixed
     */
    public static function getLevel($consumption)
    {
        $levels = cache("levels_consumption");
        if (!$levels) {
            $levels = self::where(1)->order("levelid asc")->select();
            cache("levels_consumption", $levels, 7200);
        }
        $info = [];
        foreach ($levels as $val) {
            $info = $val;
            if ($val['upgrade_score'] > $consumption) {
                break;
            }
        }
        return $info;
    }

    /**
     * 获取指定等级需要的分数
     * @param $levelid
     * @author 似水星辰 [ 2630481389@qq.com ]
     * @created 2019/4/27 11:25
     * @editor XX [ xxx@qq.com ]
     * @updated 2019.03.30
     * @return mixed
     */
    public static function getLevelScore($levelid)
    {
        return self::where("levelid", $levelid)->cache(3600)->value("upgrade_score");
    }


    /**
     * 获取升级进度
     * @param $upgrade_score
     * @param $consumption
     * @author 似水星辰 [ 2630481389@qq.com ]
     * @created 2019/4/27 11:23
     * @return int
     */
    public static function getProgress($upgrade_score, $consumption)
    {
        $cha = $upgrade_score + 1 - $consumption;
        $baifen = ($consumption / ($cha + $consumption)) * 100;
        return intval($baifen);
    }

    /**
     * 获取消费等级
     * @param $consumption
     * @author 似水星辰 [ 2630481389@qq.com ]
     * @created 2019/4/27 11:24
     * @return type
     */
    public static function getMLevel($consumption)
    {
        return self::getLevel($consumption);
    }

    /**
     * 获取收益等级
     * @param $votes_total
     * @author 似水星辰 [ 2630481389@qq.com ]
     * @created 2019/4/27 11:24
     * @return array|mixed
     */
    public static function getVLevel($votes_total)
    {
        return LevelVotes::getLevel($votes_total);
    }
}