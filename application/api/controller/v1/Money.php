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

namespace app\api\controller\v1;

use app\api\controller\Base;
use app\user\model\User;
use app\user\model\MoneyLog;
use think\Db;
use service\ApiReturn;

/**
 * 余额以及积分接口
 * Class Money
 * @package app\api\controller
 * @author 似水星辰 [ 2630481389@qq.com ]
 * @since 2019/4/23 19:33
 */
class Money extends Base
{
    /**
     * 我的余额
     * @return void
     * @since 2019/4/23 18:21
     * @author 似水星辰 [ 2630481389@qq.com ]
     */
    public function get_my_money($data = [], $user = [])
    {
        $money = db('user')->where('id', $user['id'])->find();
        return ApiReturn::r(1, $this->filter($money, $this->fname), '请求成功');
    }

    /**
     * 获取余额交易明细
     * @return void
     * @throws DbException
     * @author 似水星辰 [ 2630481389@qq.com ]
     * @since 2019/4/23 18:30
     */
    public function get_money_detail($data = [], $user = [])
    {
        if ($data['date']) {
            $start_time = strtotime($data['date']);
            $end_time = strtotime('+1 month', $start_time);
            $whereTime = "create_time BETWEEN $start_time AND $end_time";
        }

        $data = db('user_money_log')->where('user_id', $user['id'])->where($whereTime)->order('aid', 'desc')->paginate()->each(function ($item) {
            $item['change_type'] = MoneyLog::get_type($item['change_type']);
            return $item;
        });

        if ($data) {
            return ApiReturn::r(1, $data, '请求成功');
        }
        return ApiReturn::r(1, [], '请求成功');
    }

    /**
     * 上传微信或支付宝提现账号信息
     * @author 似水星辰 [ 2630481389@qq.com ]
     * @created 2019/9/13 20:46
     */
    public function bind_withdraw_account($data, $user)
    {
        $data['user_id'] = $user['id'];
        $WithdrawAccount = new \app\user\model\WithdrawAccount();
        $info = $WithdrawAccount->where(['user_id' => $user['id'], 'account_type' => $data['account_type']])->find();
        // 启动事务
        Db::startTrans();
        try {
            if ($info) {
                $res1 = $WithdrawAccount->where(['user_id' => $user['id']])->update(['is_default' => 0]);
                $res = $WithdrawAccount->where(['user_id' => $user['id'], 'account_type' => $data['account_type']])->update($data);
                if (!$res || !$res1) {
                    exception('绑定失败');
                }
            } else {
                $data['status'] = 1;
                $res = $WithdrawAccount->create($data);
                if (!$res) {
                    exception('绑定失败');
                }
            }
            // 提交事务
            Db::commit();
        } catch (\Exception $e) {
            // 回滚事务
            Db::rollback();
            return ApiReturn::r(0, [], $e->getMessage());
        }
        return ApiReturn::r(1, [], '绑定成功');
    }

    /**
     * 获取绑定的提现账号
     * @param $data
     * @param $user
     * @author 似水星辰 [ 2630481389@qq.com ]
     * @created 2019/9/13 21:39
     */
    public function get_withdraw_account($data, $user)
    {
        $res = \app\user\model\WithdrawAccount::where(['user_id' => $user['id'], 'account_type' => $data['account_type']])->find();
        if (!$res) {
            return ApiReturn::r(0, [], '暂未绑定');
        }
        $result = $this->filter($res, $this->fname);
        return ApiReturn::r(1, $result, '请求成功');
    }

    /**
     * 申请提现
     * @return void
     * @since 2019/4/23 19:05
     * @editor 李志豪 [ 995562569@qq.com ]
     * @updated 2019.05.21
     * @author 似水星辰 [ 2630481389@qq.com ]
     */
    public function withdraw($data = [], $user = [])
    {
        $money = $data['money'];
        $type = $data['type'];

        // 启动事务
        Db::startTrans();
        try {

            // 读取实时金额
            $now_money = User::where('id', $user['id'])->lock(true)->value('user_money');
            $wd_min_money = module_config('user.min_withdraw_money');

            // 提现金额起提标准判断
            if ($now_money < $wd_min_money) {
                return ApiReturn::r(0, [], '您的余额暂未达到提现标准');
            }

            // 提现金额最低标准判断
            if ($wd_min_money > $money) {
                exception('提现金额最低为' . $wd_min_money . '元');
            }

            // 提现金额不能大于余额
            if ($money > $now_money) {
                exception('余额不足，无法提现');
            }
            //提现余额为减法
            $tx_money = -$money;

            $order_no = get_order_sn('TX');
            // 变更余额记录
            $moneylog = MoneyLog::changeMoney($user['id'], $now_money, $tx_money, 4, $remark = '会员申请提现', $order_no);
            if (!$moneylog) {
                exception('更改余额失败');
            }

            //组合用户提现信息
            $account = Db::name('user_withdraw_account')->where(['user_id' => $user['id'], 'account_type' => $type])->field('id,true_name')->find();
            $withdraw_data = [
                'user_id' => $user['id'],
                'true_name' => $account['true_name'],
                'order_no' => $order_no,
                'cash_fee' => $money,
                'check_status' => 0,
                'account_type' => $type,
                'account_id' => $account['id'],
                'create_time' => time(),
            ];
            //精度计算手续费
            $withdraw_handling_type = module_config('user.withdraw_handling_type');
            if ($withdraw_handling_type == 0) {
                //固定金额手续费
                $withdraw_handling_fee = module_config('user.withdraw_handling_fee');
            }else{
                //百分比手续费
                $withdraw_handling_fee = bcmul($money,module_config('user.withdraw_handling_fee')*0.01,2);
            }
            $withdraw_data['pay_fee'] = bcsub($money, $withdraw_handling_fee, 2);
            $withdraw_data['handling_fee'] = $withdraw_handling_fee;
            // 新增提现记录
            $withdraw = Db::name('user_withdraw')->insertGetId($withdraw_data);

            if (!$withdraw) {
                exception('创建提现记录失败');
            }
            // 提交事务
            Db::commit();

        } catch (\Exception $e) {
            // 回滚事务
            Db::rollback();
            return ApiReturn::r(0, [], $e->getMessage());
        }
        return ApiReturn::r(1, [], '申请提现成功');
    }

    /**
     * 获取充值规则
     * @param $data
     * @param $user
     * @return \think\response\Json
     * @author 似水星辰 [ 2630481389@qq.com ]
     * @created 2019/5/8 18:49
     */
    public function recharge_rule($data, $user)
    {
        $list = \app\user\model\RechargeRule::where("status", 1)->where('group', $data['group'])->order("sort asc,id asc")->select();
        $info = [];
        foreach ($list as $val) {
            $info[] = $this->filter($val, $this->fname);
        }
        return ApiReturn::r(1, $info, '成功');
    }

}
