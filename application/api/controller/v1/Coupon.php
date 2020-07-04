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
use app\operation\model\CouponRecord as RecordModel;
use app\operation\model\Coupon as CouponModel;
use service\ApiReturn;
use think\Db;

/**
 * 优惠券接口
 * Class Coupon
 */
class Coupon extends Base
{

    /**
     * 获取指定获取方式的优惠券
     * @param $method 0系统自动发放 1首页弹窗 2手动领取
     * @return void
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @author 似水星辰 [ 2630481389@qq.com ]
     */
    public function get_coupon($data = [])
    {
        $data['number'] = $data['number'] ? $data['number'] : 3;
        $map[] = ['method', 'eq', $data['method']];
        $map[] = ['status', 'eq', 1];
        $map[] = ['end_time', 'gt', time()];
        $map[] = ['last_stock', 'gt', 0];
        $list = CouponModel::where($map)->limit($data['number'])->select();
        foreach ($list as $k => &$v) {
            $v['end_time'] = date('Y-m-d H:i:s', $v['end_time']);
            if ($data['user_id']) {
                $v['is_receive'] = RecordModel::where(['user_id' => $data['user_id'], 'cid' => $v['id']])->count();
            }
            $list[$k] = $this->filter($v, $this->fname);
        }
        if ($list) {
            return ApiReturn::r(1, $list, '请求成功');
        }

        return ApiReturn::r(1, [], '暂无数据');
    }

    /**
     * 领取优惠券
     * @param int $cid 优惠券id
     * @param int $uid 领取会员的id
     * @return void
     * @author 似水星辰 [ 2630481389@qq.com ]
     */
    public function receive_coupon($data = [], $user = [])
    {
        $is_receive = RecordModel::where(['user_id' => $user['id'], 'cid' => $data['cid']])->count();
        if($is_receive){
            return ApiReturn::r(0, [], '请勿重复领取');
        }
        // 启动事务
        Db::startTrans();
        try {
            $map[] = ['id', '=', $data['cid']];
            $map[] = ['status', '=', 1];
            $map[] = ['end_time', '>', time()];
            $map[] = ['last_stock', '>', 0];
            $info = Db::name('operation_coupon')->where($map)->field('valid_day')->lock(true)->find();
            if (!$info) {
                exception('优惠券已经被领取完了');
            }
            //减少优惠券库存
            $res = Db::name('operation_coupon')->where($map)->setDec('stock');
            if (!$res) {
                exception('优惠券已经被领取完了');
            }

            //增加会员优惠券领取记录
            $receive_data['user_id'] = $user['id'];
            $receive_data['cid'] = $data['cid'];
            $receive_data['start_time'] = time();
            $receive_data['end_time'] = $receive_data['start_time'] + 86400 * $info['valid_day'];
            $receive_data['status'] = 1;
            $res = Db::name('operation_coupon_record')->insert($receive_data);
            if (!$res) {
                exception('优惠券领取失败');
            }

            // 提交事务
            Db::commit();
        } catch (\Exception $e) {
            // 回滚事务
            Db::rollback();
            return ApiReturn::r(0, [], $e->getMessage());
        }
        return ApiReturn::r(1, [], '领取成功');
    }

    /**
     * 优惠券发现接口，例如结算时请求一下这个接口，返回可使用的优惠金额最多的一张券
     * @param $uid 会员id
     * @param $money 订单金额
     * @return void
     * @author 似水星辰 [ 2630481389@qq.com ]
     */
    public function user_find_coupon($data = [], $user = [])
    {
        $list = RecordModel::get_best_coupon($user['id'],$data['money']);
        if($data['is_single']){
            if ($list) {
                return ApiReturn::r(1, $list[0] ? $list[0] : [], '请求成功');
            }
        }
        if ($list) {
            return ApiReturn::r(1, $list, '请求成功');
        }

        return ApiReturn::r(1, [], '暂无数据');
    }


    /**
     * 优惠券列表
     * @param array $data
     * @param array $user
     * @author 似水星辰 [ 2630481389@qq.com ]
     * @created 2019/9/28 11:55
     */
    public function coupon_list($data = [], $user = [])
    {
        $type = $data['type'];
        $orderPrice = $data['order_price'] ? $data['order_price'] : 0;
        RecordModel::edit_coupon($user['id']); //修改优惠券是否过期
        $lists = RecordModel::get_coupon_list($user['id'], $type, $orderPrice);
        if ($lists) {
            foreach ($lists as &$value) {
                $value['end_time'] = date("Y-m-d H:i", $value['end_time']);
            }
            return ApiReturn::r(1, $lists, '请求成功');
        }
        return ApiReturn::r(1, [], '暂无数据信息');
    }

    /**
     * 优惠券详情
     * @param array $data
     * @param array $user
     * @author 似水星辰 [ 2630481389@qq.com ]
     * @created 2019/9/28 11:56
     */
    public function coupon_detail($data = [], $user = [])
    {
        $userCouponId = $data['user_coupon_id'];
        $where[] = ['cr.id', 'eq', $userCouponId];
        $where[] = ['cr.user_id', 'eq', $user['id']];
        $result = RecordModel::get_user_coupon($where);
        if ($result) {
            return ApiReturn::r(1, $result, '请求成功');
        }
        return ApiReturn::r(1, [], '暂无数据信息');
    }
}