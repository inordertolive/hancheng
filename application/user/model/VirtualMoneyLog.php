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
 * 余额变动记录表
 * Class Money
 * @package app\user\model
 * @author 似水星辰 [ 2630481389@qq.com ]
 * @since 2019/4/3 16:38
 */
class VirtualMoneyLog extends ThinkModel
{

    protected $table = "__USER_VIRTUAL_LOG__";

    // 自动写入时间戳
    protected $autoWriteTimestamp = true;

    //记录类型。你有新的类型，请添加到这里
    public static $types = [
        '1' => '充值',
        '2' => '消费',
        '3' => '管理员操作',
    ];

    /**
     * 获取记录类型
     * @param $id
     * @author 似水星辰 [ 2630481389@qq.com ]
     * @created 2019/4/28 9:05
     * @editor XX [ xxx@qq.com ]
     * @updated 2019.03.30
     * @return mixed
     */
    public static function get_type($id){
        return self::$types[$id];
    }

    /**
     * 会员余额变动(含充值，打赏)
     * @param int $user_id 会员ID
     * @param int $before_money 会员现余额
     * @param int $money 增加金额 负数就是减少
     * @param int $type 消费类型
     * @param int $consumption 传入此金额记录总消费
     * @return boolean
     * @throws \Exception
     */

    public static function changeMoney($user_id, $before_money, $money, $type = 1, $remark = '', $ordeNo = '', $consumption = 0)
    {
        // 启动事务
        self::startTrans();
        try {
            $after_money = bcadd($before_money, $money, 2);
            //如果变动结果小于0 则返回失败
            if ($after_money < 0) {
                throw new \Exception('变动后金额小于0');
            }

            $ret = User::where('id', $user_id)->update([
                'user_virtual_money' => $after_money,
                'total_consumption_virtual_money' => ['inc', $consumption],//记录礼物等 影响直播等级
            ]);

            if (!$ret) {
                throw new \Exception('更新会员余额失败');
            }
            $data = array(
                'user_id' => $user_id,
                'change_money' => $money,
                'before_money' => $before_money,
                'after_money' => $after_money,
                'change_type' => $type,
                'remark' => $remark,
                'order_no' => $ordeNo,
            );

            $result = self::create($data);
            if (!$result) {
                throw new \Exception('插入流水记录失败');
            }

            // 提交事务
            self::commit();
            return $after_money;
        } catch (\Exception $e) {
            // 回滚事务
            self::rollback();
            return $e->getMessage();
        }
    }

    /**
     * 获取指定用户的消费列表
     * @return mixed
     * @throws \think\exception\DbException
     */
    public static function getList($user_id)
    {
        return self::where("user_id", $user_id)->order("aid desc")->paginate();
    }

    /**
     * 获取所有会员消费记录列表
     * @return mixed
     * @throws \think\exception\DbException
     */
    public static function getAllList($map = [], $order = [])
    {
        return self::view("user_virtual_log", true)
            ->view("user", 'user_nickname', 'user_virtual_log.user_id=user.id', 'left')
            ->where($map)
            ->order($order)
            ->paginate();
    }

}
