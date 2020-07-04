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
 * 积分变动记录表
 *
 * @author 似水星辰 [2630481389@qq.com]
 */
class ScoreLog extends ThinkModel
{

    protected $table = "__USER_SCORE_LOG__";
    //记录类型。你有新的类型，请添加到这里
    public static $types = [
        '1' => '签到赠送积分',
        '2' => '商城赠送积分',
        '3' => '积分商城兑换',
        '5' => '商城抵扣',
    ];
    // 自动写入时间戳
    protected $autoWriteTimestamp = true;

    /**
     * 会员积分变动
     * @param int $user_id 会员ID
     * @param int $score 操作数值 负数就是减少
     * @param int $type 类型
     * @param string $remark 备注
     * @param int $count_score 传入这个数计入到累计收益 影响等级
     * @return boolean
     * @throws \Exception
     */
    public static function change($user_id, $score, $type = 1, $remark = '', $ordeNo = '', $count_score = 0)
    {
        self::startTrans();
        try {
            $before_score = \app\user\model\User::where('id', $user_id)->value('score');
            $after_score = bcadd($before_score, $score, 2);

            //如果变动结果小于0 则返回失败
            if ($after_score < 0) {
                throw new \Exception('变动后收益小于0');
            }

            $count_score = $score > 0 ? $score : 0;
            if ($score < 0) {
                $map = ['score', '>=', $score];
            }
            $ret = \app\user\model\User::where('id', $user_id)->where($map)->update([
                'score' => $after_score,
                'count_score' => ['inc', $count_score],
            ]);

            $data = array(
                'user_id' => $user_id,
                'change_score' => $score,
                'before_score' => $before_score,
                'after_score' => $after_score,
                'change_type' => $type,
                'remark' => $remark ? $remark : self::$types[$type],
                'order_no' => $ordeNo
            );

            $result = self::create($data);
            if (!$result || !$ret) {
                throw new \Exception('操作失败');
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
     * 获取列表
     * @param $user_id
     * @return \think\Paginator
     * @throws \think\exception\DbException
     * @author 似水星辰 [ 2630481389@qq.com ]
     * @since 2019/4/3 18:11
     */
    public static function getList($user_id)
    {
        return self::where("user_id", $user_id)->order("aid desc")->paginate();
    }

}
