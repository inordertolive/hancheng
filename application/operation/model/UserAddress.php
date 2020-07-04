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
 * 导航分类模型
 * @package app\operation\model
 */
class UserAddress extends ThinkModel
{
    // 设置当前模型对应的完整数据表名称
    protected $table = '__USER_ADDRESS__';

    // 自动写入时间戳
    protected $autoWriteTimestamp = true;

    /**
     * 获得单条收货地址信息
     * @param $where
     * @return array|false|null|\PDOStatement|string|ThinkModel
     */
    public function get_one_address($where){
        $address = UserAddress::where($where)->field("id,user_name,user_phone,user_address,is_default,province_name,city_name,country_name,detailInfo")->find();
        return $address;
    }

    public function get_region($where){
//        $region
    }

}