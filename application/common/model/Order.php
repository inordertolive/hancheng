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

use think\Db;
use think\Model as ThinkModel;

/**
 * 购物车模型
 * @package app\operation\model
 */
class Order extends ThinkModel
{

    // 设置当前模型对应的完整数据表名称
    public static $payTypes = [
        'wxpay' => '微信',
        'alipay' => '支付宝',
        'appleiap' => '苹果内购'
    ];

    // 自动写入时间戳
    public static $orderTypes = [
        1 => '现金充值',
        2 => '虚拟币充值',
        3 => '商城交易',
        4 => '购买VIP',
    ];

    //所有支付方式
    public static $pay_status = [
        0 => '未付款',
        1 => '已付款',
    ];
    //订单类型
    public static $order_status = [
        '-1' => '已取消',
        0 => '待支付',
        1 => '已支付',
        2 => '已发货',
        3 => '已完成',
        4 => '已评价',
        5 => '售后中',
        6 => '售后完成',
    ];

    //支付状态
    protected $table = '__ORDER__';

    //订单状态
    protected $autoWriteTimestamp = true;

    /**
     * 创建充值订单
     * @param array $data 下单参数数组
     * @param array $user 会员数据数组
     * @return array
     * @throws \Exception
     * @author 似水星辰 [ 2630481389@qq.com ]
     */
    public static function addRechargeOrder($data, $user)
    {
        $rule_id = $data['product_id'];
        if ($rule_id) {
            $rule = \app\user\model\RechargeRule::get($rule_id);
            if (!$rule) {
                throw new \Exception("获取充值规则失败");
            }
            $money = $rule['money'];
            $orderData['product_id'] = $data['product_id'];
        } else {
            $money = $data['order_money'];
        }

        if (!$money) {
            throw new \Exception("缺少充值金额");
        }

        //写入订单
        $order_no = get_order_sn('CZ');
        $orderData['user_id'] = $user['id'];
        $orderData['order_sn'] = $order_no;
        $orderData['order_money'] = $money;
        $orderData['payable_money'] = $data['payable_money'] ? $data['payable_money'] : $money;
        $orderData['real_money'] = 0;
        $orderData['pay_status'] = 0;
        $orderData['status'] = 0;
        $orderData['pay_type'] = $data['pay_type'];
        $orderData['order_type'] = $data['order_type'];

        $ret = self::create($orderData);
        if (!$ret) {
            throw new \Exception("创建订单失败");
        }
        return [
            'order_sn' => $order_no
        ];
    }

    /**
     * 创建商城订单
     * @param array $data 下单参数数组
     * @param array $user 会员数据数组
     * @return array
     * @throws \Exception
     * @author 似水星辰 [ 2630481389@qq.com ]
     */
    public static function addGoodsOrder($data, $user)
    {
        $order_info = json_decode($data['order_info'], true);
        Db::startTrans();
        try {
            //生成订单号
            $order_no = get_order_sn('GD');
            //添加商品订单附表信息
            $order_goods_info['order_sn'] = $order_no;
            $order_goods_info['address_id'] = $order_info['address']['address_id'];
            $order_goods_info['receiver_mobile'] = $order_info['address']['mobile'];
            $order_goods_info['receiver_address'] = $order_info['address']['address'];
            $order_goods_info['receiver_name'] = $order_info['address']['name'];
            $order_goods_info['remark'] = $data['remark'] ?? '';
            $order_goods_info['express_price'] = $order_info['express_price'] ?? 0;

            $res1 = Db::name('order_goods_info')->insert($order_goods_info);
            if (!$res1) {
                exception("保存订单附加信息失败");
            }

            $money = 0;
            //实例化商品模型
            $goodinfo = new \app\goods\model\Goods();
            $goodsku = new \app\goods\model\GoodsSku();

            //添加订单商品表信息
            foreach ($order_info['goods'] as $g) {
                // 初始化变量
                $goods = $where = $where1 = [];
                // 开始循环商品信息
                $good_info = $goodinfo->get($g['id']);
                $goods['order_sn'] = $order_no;
                $goods['goods_id'] = $g['id'];
                $goods['goods_name'] = $good_info['name'];
                $goods['shop_price'] = $good_info['shop_price'];
                $goods['sku_id'] = $g['sku_id'] ? $g['sku_id'] : 0;
                $goods['num'] = $g['number'];
                $stock = $good_info['stock'];
                $goods['goods_thumb'] = $good_info['thumb'];
                $goods['order_status'] = 0;
                if ($goods['sku_id']) {
                    //如果是sku商品，则查询sku的价格和库存
                    $sku_info = $goodsku->get(['sku_id' => $goods['sku_id'], 'goods_id' => $g['id']]);
                    $goods['shop_price'] = $sku_info['shop_price'];
                    $stock = $sku_info['stock'];
                }
                if ($stock < $g['number']) {
                    exception($sku_info['key_name'] . ",库存不足，无法下单");
                }
                $goods['sku_name'] = $sku_info['key_name'];
                //计算商品总价
                $goods['goods_money'] = bcmul($goods['shop_price'], $g['number'], 2);

                $money = bcadd($money, $goods['goods_money'], 2);

                if ($goods['sku_id']) {
                    // 减sku库存
                    $where[] = ['sku_id', '=', $goods['sku_id']];
                    $where[] = ['stock', '>=', $g['number']];
                    $res3 = $goodsku->where($where)->setDec('stock', $g['number']);

                    // 增加sku销量
                    $goodsku->where(['sku_id' => $goods['sku_id']])->setInc('sales_num', $g['number']);
                    if (!$res3) {
                        exception($sku_info['key_name'] . ",库存不足，无法下单");
                    }
                }
                // 减主商品库存
                $where1[] = ['id', '=', $g['id']];
                $where1[] = ['stock', '>=', $g['number']];
                $res4 = $goodinfo->where($where1)->setDec('stock', $g['number']);
                if (!$res4) {
                    exception("库存不足，无法下单");
                }
                // 增加总销量
                $goodinfo->where(['id' => $g['id']])->setInc('sales_sum', $g['number']);

                $goods_list[] = $goods;
            }
            //插入订单商品表
            $res2 = Db::name('order_goods_list')->insertAll($goods_list);
            if (!$res2) {
                exception("保存订单商品失败");
            }

            $payable_money = $money;
            //如果提交了优惠券id，则查询数据库中的优惠券
            if ($data['coupon_id']) {
                $cou = new \app\operation\model\CouponRecord();
                $coupon = $cou->get_user_coupon(['cr.user_id' => $user['id'], 'cr.id' => $data['coupon_id'], 'cr.status' => 1]);
                if (!$coupon) {
                    exception("优惠券无效，请重新下单");
                }
                $payable_money = bcsub($money, $coupon['money'], 2);
                $res5 = $cou->where(['id' => $data['coupon_id'], 'status' => 1])->update(['status' => 3, 'use_time' => time(), 'order_sn' => $order_no]);
                if (!$res5) {
                    exception("优惠券无效，请重新下单");
                }
            }
            //如果有运费，加上
            if ($order_info['express_price']) {
                $payable_money = bcadd($payable_money, $order_info['express_price'], 2);
            }

            // 计算出来的金额和提交过的来金额做对比，一致才往下走
            if ($payable_money !== $data['payable_money']) {
                exception("金额校验失败");
            }

            // 组装订单信息
            $orderData['user_id'] = $user['id'];
            $orderData['order_sn'] = $order_no;
            $orderData['order_money'] = bcadd($money, $order_info['express_price'], 2);
            $orderData['payable_money'] = $payable_money;
            $orderData['status'] = 0;
            $orderData['real_money'] = 0;
            $orderData['pay_status'] = 0;
            $orderData['pay_type'] = $data['pay_type'] ?? '';
            $orderData['coupon_id'] = $data['coupon_id'] ? $data['coupon_id'] : 0;
            $orderData['coupon_money'] = $coupon['money'] ? $coupon['money'] : 0;
            $orderData['order_type'] = $data['order_type'];
            // 插入订单信息
            $ret = self::create($orderData);
            if (!$ret) {
                exception("创建订单失败");
            }

            Db::commit();
        } catch (\Exception $e) {
            Db::rollback();
            exception($e->getMessage());
            return false;
        }
        return [
            'order_sn' => $order_no
        ];
    }

    /**
     * 订单异步回调
     * @param string $order_no 订单号
     * @param int $pay_type 支付方式
     * @param string $transaction_id 第三方订单号
     * @return boolean
     * @author 似水星辰 [ 2630481389@qq.com ]
     */

    public static function verify($order_no, $pay_type, $transaction_id = '', $total_fee)
    {
        set_time_limit(0);
        $order = self::where('order_sn', $order_no)->find();
        if ($order['pay_status'] > 0) {
            return true;
        }
        if (!$order) {
            return false;
        }
        //加进程锁
        $locKey = "lock_" . $order_no;
        $redis = \app\common\model\Redis::handler();
        $lock = $redis->get($locKey);
        if ($lock) {
            return false;
        }
        $redis->setnx($locKey, 1);
        //使用事务
        Db::startTrans();
        try {
            $upOrder = [
                'pay_status' => 1,
                'pay_type' => $pay_type,
                'pay_time' => time(),
                'transaction_id' => $transaction_id,
            ];
            if ($order['order_type'] == 1 && $order['order_type'] == 2) {
                //如果是充值，则直接完成订单
                $upOrder['status'] = 3;
            } else if ($order['order_type'] == 3) {
                //商城订单改为已支付
                $upOrder['status'] = 1;
            }

            if ($pay_type == 'wxpay') {
                //微信要除以100
                $upOrder['real_money'] = $total_fee / 100;
            } else {
                $upOrder['real_money'] = $total_fee;
            }
            $res = self::where(['order_sn' => $order_no, 'status' => 0])->update($upOrder);
            if (!$res) {
                exception('订单处理异常');
            }
            switch ($order['order_type']) {
                case 1:
                    $user = \app\user\model\User::get($order['user_id']);
                    $remark = '现金充值订单';

                    if ($order['product_id']) {
                        $money = \app\user\model\RechargeRule::where('id', $order['product_id'])->value('add_money');
                    } else {
                        $money = $order['order_money'];
                    }

                    $after_money = \app\user\model\MoneyLog::changeMoney($user['id'], $user['user_money'], $money, 1, $remark, $order_no);
                    if (!$after_money) {
                        exception('订单处理异常');
                    }
                    break;
                case 2:
                    $user = \app\user\model\User::get($order['user_id']);
                    $remark = '虚拟币充值订单';

                    if ($order['product_id']) {
                        $money = \app\user\model\RechargeRule::where('id', $order['product_id'])->value('add_money');
                    } else {
                        $money = $order['order_money'];
                    }

                    $after_money = \app\user\model\VirtualMoneyLog::changeMoney($user['id'], $user['user_virtual_money'], $money, 1, $remark, $order_no);
                    if (!$after_money) {
                        exception('订单处理异常');
                    }
                    break;
                case 3:
                    Db::name('order_goods_list')->where(['order_sn' => $order_no, 'order_status' => 0])->update(['order_status' => 1]);
                    break;
                default:

                    break;
            }
            Db::commit();
        } catch (\Exception $e) {
            Db::rollback();
            $redis->delete($locKey);
            return false;
        }

        $redis->delete($locKey);
        return true;
    }
}
