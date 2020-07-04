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
namespace app\common\model;
use think\Facade\Config;
use think\Facade\Cache;

/**
 * 独立REDIS设置
 * Class Redis
 * @package app\common\model
 * @author 似水星辰 [ 2630481389@qq.com ]
 * @since 2019/4/8 8:42
 */
class Redis {
    /**
     * 创建一个新的Cache对象
     * @author 似水星辰 [ 2630481389@qq.com ]
     * @since 2019/4/8 8:42
     * @return \think\cache\Driver
     */
    public static function init(){
        $config = Config::get("redis.");
        return Cache::connect($config);
    }

    /**
     * 获取独立redis句柄
     * @author 似水星辰 [ 2630481389@qq.com ]
     * @since 2019/4/8 8:42
     * @return object
     */
    public static function handler(){
        return self::init()->handler();
    }
}

