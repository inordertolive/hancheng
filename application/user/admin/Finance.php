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
namespace app\user\admin;

use app\admin\admin\Base;
use app\user\model\BobiLog;
use app\user\model\MoneyLog;
use app\user\model\User;
use app\user\model\Raward;
use app\user\model\VirtualMoneyLog;
use app\user\model\VotesLog;
use think\Db;
use service\Format;

/**
 * 财务统计
 * Class Finance
 * @package app\user\admin
 * @author 似水星辰 [ 2630481389@qq.com ]
 * @since 2019/4/3 16:02
 */
class Finance extends Base
{

    /**
     * 财务统计
     * @author 似水星辰 [ 2630481389@qq.com ]
     * @since 16:02
     * @return mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function index()
    {
        $t1 = strtotime(date("Y-m"));
        $t2 = strtotime("+1 month", $t1) - 1;

        $count = [];
        $count['month_order_price'] = Db::name("order")->whereBetween("create_time", [$t1, $t2])->where("pay_status", '>', 0)->sum('paid_money');

        $count['month_cash_price'] = Db::name("user_withdraw")->whereBetween("create_time", [$t1, $t2])->where("cash_status", 1)->sum('cash_fee');

        $t3 = strtotime("-1 month", $t1);
        $t4 = $t1 - 1;

        $count['prevmonth_order_price'] = Db::name("order")->whereBetween("create_time", [$t3, $t4])->where("pay_status", '>', 0)->sum('paid_money');

        $count['prevmonth_cash_price'] = Db::name("user_withdraw")->whereBetween("create_time", [$t3, $t4])->where("cash_status", 1)->sum('cash_fee');

        //根据日统计订单

        $where = "create_time > $t1 and create_time < $t2 ";
        $order = Db::name("order")
            ->fieldRaw("FROM_UNIXTIME(create_time, '%Y-%m-%d') as day, sum(paid_money) as price")
            ->whereBetween("create_time", [$t1, $t2])
            ->where("pay_status", '>', 0)
            ->group("FROM_UNIXTIME(create_time, '%Y-%m-%d')")
            ->select();

        //根据日统计提现
        $cash = Db::name("user_withdraw")
            ->fieldRaw("FROM_UNIXTIME(create_time, '%Y-%m-%d') as day, sum(cash_fee) as price")
            ->whereBetween("create_time", [$t1, $t2])
            ->where("cash_status", 1)
            ->group("FROM_UNIXTIME(create_time, '%Y-%m-%d')")
            ->select();
        $this->assign("count", $count);
        $this->assign("order", $order);
        $this->assign("cash", $cash);
        return $this->fetch(); // 渲染模板	
    }

    /**
     * 充值消费
     * @author 似水星辰 [ 2630481389@qq.com ]
     * @created 2019/5/9 9:35
     * @return mixed
     * @throws \think\exception\DbException
     */
    public function money_log()
    {
        cookie('__forward__', $_SERVER['REQUEST_URI']);
        // 查询
        $map = $this->getMap();        // 排序
        $order = $this->getOrder('user_money_log.aid DESC');

        $data_list = MoneyLog::getAllList($map, $order);

        $types = MoneyLog::$types;
        $fields = [
            ['aid', '序号'],
            ['user_id', '会员ID'],
            ['user_nickname', '会员名称'],
            ['before_money', '变动前金额'],
            ['change_money', '变动金额'],
            ['after_money', '变动后金额'],
            ['change_type', '类型', '', '', $types],
            ['create_time', '变动时间'],
            ['remark', '备注'],
        ];
        return Format::ins()
            ->hideCheckbox()
            ->addColumns($fields)
            ->setTopButton(['title' => '手动充值余额', 'href' => ['add'], 'icon' => 'fa fa-plus pr5', 'class' => 'btn btn-sm mr5 btn-success btn-flat'])
            ->setTopButton(['title' => '手动充值虚拟币', 'href' => ['virtual_add'], 'icon' => 'fa fa-plus pr5', 'class' => 'btn btn-sm mr5 btn-success btn-flat'])
            ->setTopButton(['title' => '虚拟币充值消费记录', 'href' => ['virtual_log'], 'icon' => 'fa fa-list pr5', 'class' => 'btn btn-sm mr5 btn-success btn-flat'])
            ->setData($data_list)
            ->fetch();
    }

    public function virtual_log()
    {
        cookie('__forward__', $_SERVER['REQUEST_URI']);
        // 查询
        $map = $this->getMap();        // 排序
        $order = $this->getOrder('user_virtual_log.aid DESC');

        $data_list = VirtualMoneyLog::getAllList($map, $order);

        $types = VirtualMoneyLog::$types;
        $fields = [
            ['aid', '序号'],
            ['user_id', '会员ID'],
            ['user_nickname', '会员名称'],
            ['before_money', '变动虚拟币'],
            ['change_money', '变动虚拟币'],
            ['after_money', '变动后虚拟币'],
            ['change_type', '类型', '', '', $types],
            ['create_time', '变动时间'],
            ['remark', '备注'],
        ];

        return Format::ins()
            ->hideCheckbox()
            ->addColumns($fields)
            ->setTopButton(['title' => '手动充值余额', 'href' => ['add'], 'icon' => 'fa fa-plus pr5', 'class' => 'btn btn-sm mr5 btn-success btn-flat'])
            ->setTopButton(['title' => '手动充值虚拟币', 'href' => ['virtual_add'], 'icon' => 'fa fa-plus pr5', 'class' => 'btn btn-sm mr5 btn-success btn-flat'])
            ->setTopButton(['title' => '余额消费记录', 'href' => ['money_log'], 'icon' => 'fa fa-list pr5', 'class' => 'btn btn-sm mr5 btn-success btn-flat'])
            ->setData($data_list)
            ->fetch();
    }

    /**
     * 手动充值余额
     * @author 似水星辰 [ 2630481389@qq.com ]
     * @created 2019/5/9 9:36
     * @return mixed
     */
    public function add()
    {
        // 保存文档数据
        if ($this->request->isAjax()) {
            $data = $this->request->post();
            // 启动事务
            Db::startTrans();
            try{
                $user = User::where('id', $data['user_id'])->lock(true)->field('id,user_money')->find();
                if (!$user) {
                    $this->error('会员不存在');
                }
                $money = $data['money'];
                $remark = '系统充值余额' . $money . ',操作管理员工号:' . UID;
                $ordeNo = get_order_sn('CZ');
                $money = bcadd($money, 0, 2);
                MoneyLog::changeMoney($user['id'], $user['user_money'], $money, 3, $remark, $ordeNo);

                // 提交事务
                Db::commit();
            } catch (\Exception $e) {
                // 回滚事务
                Db::rollback();
                $this->error($e->getMessage());
            }
            $this->success('充值成功');
        }
        $fields = [
            ['type' => 'text', 'name' => 'user_id', 'title' => '会员ID'],
            ['type' => 'text', 'name' => 'money', 'title' => '充值金额'],
        ];

        $this->assign('page_title', '手动充值');
        $this->assign('form_items', $fields);
        return $this->fetch('admin@public/add');
    }

    /**
     * 手动充值虚拟币
     * @author 似水星辰 [ 2630481389@qq.com ]
     * @created 2019/5/9 9:36
     * @return mixed
     */
    public function virtual_add()
    {
        // 保存文档数据
        if ($this->request->isAjax()) {
            $data = $this->request->post();
            // 启动事务
            Db::startTrans();
            try{
                $user = User::where('id', $data['user_id'])->lock(true)->field('id,user_virtual_money')->find();
                if (!$user) {
                    $this->error('会员不存在');
                }
                $money = $data['money'];
                $remark = '系统充值虚拟币' . $money . ',操作管理员工号:' . UID;
                $ordeNo = get_order_sn('CZ');
                $money = bcadd($money, 0, 2);
                VirtualMoneyLog::changeMoney($user['id'], $user['user_virtual_money'], $money, 3, $remark, $ordeNo);

                // 提交事务
                Db::commit();
            } catch (\Exception $e) {
                // 回滚事务
                Db::rollback();
                $this->error($e->getMessage());
            }
            $this->success('充值成功');
        }
        $fields = [
            ['type' => 'text', 'name' => 'user_id', 'title' => '会员ID'],
            ['type' => 'text', 'name' => 'money', 'title' => '充值金额'],
        ];

        $this->assign('page_title', '手动充值虚拟币');
        $this->assign('form_items', $fields);
        return $this->fetch('admin@public/add');
    }
}
