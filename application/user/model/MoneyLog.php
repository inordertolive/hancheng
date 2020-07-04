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
class MoneyLog extends ThinkModel
{

    protected $table = "__USER_MONEY_LOG__";

    // 自动写入时间戳
    protected $autoWriteTimestamp = true;

    //记录类型。你有新的类型，请添加到这里
    public static $types = [
        '1' => '会员充值',
        '2' => '会员消费',
        '3' => '管理员操作',
        '4' => '会员提现',
        '5' => '管理员拒绝提现，返还金额',
        '6' => '会员分成',
    ];

    /**
     * 获取记录类型
     * @param $id
     * @return mixed
     * @author 似水星辰 [ 2630481389@qq.com ]
     * @created 2019/4/28 9:05
     * @editor XX [ xxx@qq.com ]
     * @updated 2019.03.30
     */
    public static function get_type($id)
    {
        return self::$types[$id];
    }

    /**
     * 会员余额变动(含充值，打赏)
     * @param int $user_id 会员ID
     * @param int $before_money 会员现余额
     * @param int $money 增加金额 负数就是减少
     * @param int $type 消费类型
     * @param int $consumption 传入此金额记录总消费
     * @editor 李志豪 [ 995562569@qq.com ]
     * @updated 2019.05.21
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
                throw new \Exception('金额不足');
            }
            if ($money < 0) {
                $map[] = ['user_money', '>=', $money];
            }

            $ret = User::where('id', $user_id)->where($map)->update([
                'user_money' => $after_money,
                'total_consumption_money' => ['inc', $consumption],//总消费记录
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
        } catch (\Exception $e) {
            // 回滚事务
            self::rollback();
            return false;
        }
        return true;
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
        return self::view("user_money_log", true)
            ->view("user", 'user_nickname', 'user_money_log.user_id=user.id', 'left')
            ->where($map)
            ->order($order)
            ->paginate();
    }

}
