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
 * 会员认证模型
 * Class Certified
 * @package app\user\model
 * @author 似水星辰 [ 2630481389@qq.com ]
 * @since 2019/4/3 11:20
 */
class Certified extends ThinkModel {

    // 设置当前模型对应的完整数据表名称
    protected $table = '__USER_CERTIFIED__';

    // 自动写入时间戳
    protected $autoWriteTimestamp = true;

    /**
     * 获取会员认证列表
     * @param array $map 筛选条件
     * @param array $order 排序
     * @param int $model
     * @return mixed
     * @throws \think\exception\DbException
     * @author 似水星辰 [ 2630481389@qq.com ]
     */
    public static function getList($map = [], $order = []) {

        $data_list = self::where($map)
            ->order($order)
            ->paginate();
        return $data_list;
    }

    /**
     * 获取单个会员和会员认证信息
     * @param string $id 会员id
     * @param array $map 查询条件
     * @return mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @author 似水星辰 [ 2630481389@qq.com ]
     */
    public static function getOne($id = '', $map = []) {
        return self::view('user', true)
            ->view("user_certified", 'name', 'user.id=user_certified.user_id', 'left')
            ->where('user.id', $id)
            ->where($map)
            ->find();
    }

}
