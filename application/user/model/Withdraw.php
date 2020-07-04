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

use \think\Db;
use \app\user\model\User;

/**
 * 提现控制器
 * Class Withdraw
 * @package app\user\model
 * @author 似水星辰 [ 2630481389@qq.com ]
 * @since 2019/4/3 18:31
 */
class Withdraw extends \think\Model
{

    protected $table = '__USER_WITHDRAW__';

    // 自动写入时间戳
    protected $autoWriteTimestamp = true;

    /**
     * 创建一个提现,提现并不会写入 会员流水，但会多一个冻结金额
     * @param int $user 会员对象
     * @param float $cash_fee 提现金额
     * @param array $ext 扩展字段
     * @return boolean|string
     * @throws \Exception
     * @author 晓风<215628355@qq.com>
     */
    public static function addCash($user, $cash_fee, $ext = [])
    {
        $default = [
            'account_id' => '',
            'account_type' => '',
            'true_name' => '',
        ];
        $ext = array_merge($default, $ext);

        if ($user['lock_votes'] > 0) {
            return '有一笔进行中的提现，暂时无法提现';
        }
        if ($cash_fee > $user['votes']) {
            return '提现金额超出收益上限';
        }
        $min = config('cash_min_money');
        if ($cash_fee < $min) {
            return '提现金额最低' . $min . '元';
        }
        $poundage_pro = config('cash_poundage_pro');
        $poundage = bcmul($cash_fee, $poundage_pro / 100, 2);
        $pay_fee = bcsub($cash_fee, $poundage, 2);

        // 启动事务
        Db::startTrans();
        try {
            $ret = User::where('id', $user['id'])->update([
                'votes' => ['dec', $cash_fee],
                'lock_votes' => ['inc', $cash_fee]
            ]);
            if (!$ret) {
                throw new \Exception('更新会员金额失败');
            }
            $data = array(
                'order_no' => get_order_sn(),
                'user_id' => $user['id'],
                'cash_fee' => $cash_fee,
                'pay_fee' => $pay_fee,
                'poundage' => $poundage,
                'true_name' => $ext['true_name'],
                'account_type' => $ext['account_type'],
                'account_id' => $ext['account_id'],
                'create_time' => time()
            );
            $result = self::insert($data);
            if (!$result) {
                throw new \Exception('插入流水记录失败');
            }
            Db::commit();
            return true;
        } catch (\Exception $e) {
            Db::rollback();
            return $e->getMessage();
        }
    }

    /**
     * 获取提现列表
     * @param array $map
     * @param array $order
     * @return bool|object|null
     * @author 晓风<215628355@qq.com>
     */
    public static function getList($map = [], $order = [])
    {
        return self::view('user_withdraw', true)
            ->view('user_withdraw_account', 'account_type,account_id,qrcode', 'user_withdraw.account_id = user_withdraw_account.id')
            ->where($map)
            ->order($order)
            ->paginate();
    }


    /**
     * 审核拒绝回退
     * @param int $uid 会员ID
     * @param float $cash_fee 提现金额
     * @param int $aid 提现表ID
     * @param string $rason 拒绝原因
     * @return boolean
     * @throws \Exception
     * @author 晓风<215628355@qq.com>
     */
    function checkBack($uid, $cash_fee, $aid, $rason)
    {

        Db::startTrans();
        try {
            $ret = User::where('id', $uid)->where("freeze_money", 'egt', $cash_fee)->update([
                'votes' => ['inc', $cash_fee],
                'freeze_money' => ['dec', $cash_fee]
            ]);
            if (!$ret) {
                throw new \Exception('更新会员金额失败');
            }
            $data = [
                'check_status' => 2,
                'check_reason' => $rason,
                'check_time' => time(),
            ];
            $result = self::where('id', $aid)->where("check_status", 0)->where("cash_status", 0)->update($data);
            if (!$result) {
                throw new \Exception('更新记录失败');
            }
            Db::commit();
            return true;
        } catch (\Exception $e) {
            Db::rollback();
            return $e->getMessage();
        }
    }

    /**
     * 转账失败回退
     * @param int $uid 会员ID
     * @param float $cash_fee 提现金额
     * @param int $aid 提现表ID
     * @param string $rason 失败原因
     * @return boolean
     * @throws \Exception
     * @author 晓风<215628355@qq.com>
     */
    function cashBack($uid, $cash_fee, $aid, $rason)
    {

        Db::startTrans();
        try {
            $ret = User::where('id', $uid)->where("lock_votes", $cash_fee)->update([
                'votes' => ['inc', $cash_fee],
                'lock_votes' => ['dec', $cash_fee]
            ]);
            if (!$ret) {
                throw new \Exception('更新会员金额失败');
            }
            $data = [
                'cash_status' => 2,
                'cash_reason' => $rason,
                'cash_time' => time(),
            ];
            $result = self::where('aid', $aid)->where("check_status", 1)->where("cash_status", 0)->update($data);
            if (!$result) {
                throw new \Exception('更新记录失败');
            }
            Db::commit();
            return true;
        } catch (\Exception $e) {
            Db::rollback();
            return $e->getMessage();
        }
    }

    /**
     * 转账成功 转出钱
     * @param array|object $info 提现表数据
     * @return boolean
     * @throws \Exception
     * @author 晓风<215628355@qq.com>
     */
    function cashSuccess($info, $is_auto)
    {

        $uid = $info['user_id'];
        $cash_fee = $info['cash_fee'];
        $aid = $info['aid'];
        Db::startTrans();
        try {

            $ret = User::where('id', $uid)->where("lock_votes", $cash_fee)->update([
                'votes' => ['inc', $cash_fee],
                'lock_votes' => ['dec', $cash_fee]
            ]);
            if (!$ret) {
                throw new \Exception('更新会员金额失败');
            }
            $data = [
                'cash_status' => 1,
                'cash_time' => time(),
            ];
            $result = self::where('aid', $aid)->where("check_status", 1)->where("cash_status", 0)->update($data);
            if (!$result) {
                throw new \Exception('更新记录失败');
            }
            $user = User::get($uid);
            $cash_fee = 0 - $cash_fee;
            VotesLog::changeVotes($uid, $user['votes'], $cash_fee, 1, '提现', $info['order_no']);

            //是否执行自动提现
            if (!$is_auto) {
                goto E;
            }
            $order_id = '';
            //执行第三方自动提现脚本
            switch ($info['account_type']) {
                //微信
                case 1:
                    $check = ThirdBind::where("wx_unionid", $info['account_id'])->find();
                    if (empty($check['mp_openid'])) {
                        throw new \Exception("提现账户未绑定公众号，无法继续转账");
                    }
                    //必须找到公众号的OPENID
                    $data = [
                        "openid" => $check['mp_openid'],
                        "amount" => $info['pay_fee'],
                        "partner_trade_no" => $info['order_no'],
                        "re_user_name" => $info['true_name'],
                        "desc" => "提现转入" . $uid . '_手续费￥' . $info['poundage']
                    ];
                    $result = plugin_action("WeChat", "CodePay", "transfers", $data);
                    $order_id = $result["payment_no"];

                case 2 :
                    //支付宝
                    $ret = plugin_action("Alipay", 'Aop', 'AlipayFundTransToaccountTransferRequest', [
                        'payee_account' => $info['account_id'],
                        'amount' => $info['pay_fee'],
                        'out_trade_no' => $info['order_no'],
                        'alipay_username' => $info['true_name'],
                        'payer_show_name' => '星说',
                        'remark' => "提现转入" . $uid . '_手续费￥' . $info['poundage'],
                    ]);
                    $order_id = $ret['order_id'];
                    break;
                default :
                    throw new \Exception('目前不支持此账户类型自动提现,请人工转账');
                    break;
            }
            if ($order_id) {
                self::where('aid', $aid)->update(['order_id' => $order_id]);
            }
            E:
            Db::commit();
            return true;//支付宝打款失败不报异常
        } catch (\Exception $e) {
            Db::rollback();
            return $e->getMessage();
        }
    }

}
