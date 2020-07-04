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
use app\user\model\Withdraw as WithdrawModel;
use service\Format;

/**
 * 提现管理
 * Class Withdraw
 * @package app\user\admin
 * @author 似水星辰 [ 2630481389@qq.com ]
 * @since 2019/4/3 18:33
 */
class Withdraw extends Base
{

    /**
     * 提现列表
     * @return mixed
     * @since 2019/4/3 18:33
     * @author 似水星辰 [ 2630481389@qq.com ]
     */
    public function index()
    {
        cookie('__forward__', $_SERVER['REQUEST_URI']);
        // 查询
        $map = $this->getMap();        // 排序
        $order = $this->getOrder('id DESC');
        $fields = [
            ['id', 'ID'],
            ['user_id', '会员ID'],
            ['true_name', '收款人姓名'],
            ['account_type', '账户类型', 'status', '', ['--', '微信', '支付宝', '银行卡']],
            ['account_id', '账户'],
            ['qrcode', '收款二维码', 'picture'],
            ['cash_fee', '提现金额', 'text'],
            ['poundage', '手续费', 'text'],
            ['pay_fee', '转账金额', 'text'],
            ['create_time', '创建时间'],
            ['check_status', '审核状态', 'status', '', ['未审核', '已审核', '已拒绝']],
            ['check_time', '审核时间', 'datetime'],
            ['cash_status', '转账状态', 'status', '', ['未转账', '已转账', '转账异常']],
            ['cash_time', '转账时间', 'datetime'],
            ['order_id', '第三方订单号'],
            ['right_button', '操作', 'btn']
        ];
        $data_list = WithdrawModel::getList($map, $order);
        return Format::ins()
            ->addColumns($fields)
            ->setRightButton(['ident' => 'check', 'title' => '审核', 'href' => ['check', ['id' => '__id__']], 'icon' => 'fa fa-check pr5', 'class' => 'btn btn-xs mr5 btn-default btn-flat'])
            ->setRightButton(['ident' => 'pay', 'title' => '付款', 'href' => ['pay', ['id' => '__id__']], 'icon' => 'fa fa-close pr5', 'class' => 'btn btn-xs mr5 btn-default btn-flat'])
            ->setData($data_list)
            ->fetch();
    }

    /**
     * 审核
     * @param type $aid
     * @return type
     * @author 似水星辰 [ 2630481389@qq.com ]
     */
    public function check($id = 0)
    {

        if ($id === 0)
            $this->error('缺少参数');
        $info = WithdrawModel::get($id);
        // 保存文档数据
        if ($this->request->isAjax()) {
            if ($info['check_status'] > 0) {
                $this->error('您已经审核过了，不能再次审核');
            }
            // 验证
            $check_status = input('post.check_status/d', 0);
            $check_reason = input('post.check_reason/', '');
            if (!in_array($check_status, [1, 2])) {
                $this->error('请选择正确的审核状态');
            }
            if ($check_status == 2 && !$check_reason) {
                $this->error('请填写拒绝原因');
            }
            //回退操作
            if ($check_status == 2) {
                $ret = WithdrawModel::checkBack($info['user_id'], $info['cash_fee'], $id, $check_reason);
                if ($ret !== true) {
                    $this->error('操作失败' . $ret);
                }
                $this->success('操作成功','index');
            }
            //不回退直接改状态
            $data = ['check_status' => 1, 'check_time' => time()];
            $res = WithdrawModel::where("id", $id)->update($data);
            if ($res) {
                $this->success('操作成功','index');
            } else {
                $this->error('操作失败');
            }
        }

        $fields = [
            ['type' => 'hidden', 'name' => 'id'],
            ['type' => 'radio', 'name' => 'check_status', 'title' => '审核状态', 'extra'=>[1 => '审核通过', 2 => '审核拒绝'], 'value' => 1],
            ['type' => 'textarea', 'name' => 'check_reason', 'title' => '拒绝原因', 'tips' => '如果拒绝，请填写原因'],
        ];

        $this->assign('page_title', '审核提现');
        $this->assign('form_items', $fields);
        return $this->fetch('../../admin/view/public/add');
    }

    /**
     * 出纳
     * @param type $aid
     * @return type
     * @author 晓风<215628355@qq.com>
     */
    public function pay($aid = 0)
    {

        if ($aid === 0)
            $this->error('缺少参数');
        $info = WithdrawModel::get($aid);
        // 保存文档数据
        if ($_POST) {
            if ($info['check_status'] != 1) {
                $this->error('请先审核通过再进行此操作');
            }

            if ($info['cash_status'] > 0) {
                $this->error('您已经操作过转账了，不能再次操作');
            }
            // 验证
            $cash_status = input('post.cash_status/d', 0);
            $is_auto = input('post.is_auto/d', 0);
            $cash_reason = input('post.cash_reason/', '');
            $password = input("post.password");
            $checkPass = 'zzebz_ChuNa!@';
            if ($password !== $checkPass) {
                $this->error('操作密码不正确');
            }

            if (!in_array($cash_status, [1, 2])) {
                $this->error('请选择正确的转账状态');
            }
            if ($cash_status == 2 && !$cash_reason) {
                $this->error('请填写拒绝原因');
            }
            //回退操作
            if ($cash_status == 2) {
                $ret = WithdrawModel::cashBack($info['user_id'], $info['cash_fee'], $aid, $cash_reason);
                if ($ret !== true) {
                    $this->error('操作失败' . $ret);
                }
                $this->success('操作成功');
            }
            //成功操作
            $ret = WithdrawModel::cashSuccess($info, $is_auto);
            if ($ret === true) {
                $this->success('操作成功');
            } else {
                $this->error('操作失败' . $ret);
            }
        }

        return ZBuilder::make('form')
            ->addFormItems([
                ['hidden', 'aid'],
                ['radio', 'cash_status', '转账状态', '必填', [1 => '已转账', 2 => '转账失败']],
                ['textarea', 'cash_reason', '失败原因', '必填'],
                ['radio', 'is_auto', '自动转账', '目前仅支付宝支持自动转账', [0 => '人工', 1 => '自动'], 0],
                ['password', 'password', '出纳操作密码', '必填'],
            ])
            ->setFormData($info)
            ->fetch();
    }

}
