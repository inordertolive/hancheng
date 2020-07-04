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
class Address extends ThinkModel
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
        $address = Address::where($where)->field("address_id,name,mobile,address,is_default,province,city,district,province_id,city_id,district_id,postal_code")->find();
        return $address;
    }
}