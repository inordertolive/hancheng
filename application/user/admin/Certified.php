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
use app\operation\model\SystemMessage as SystemMessageModel;
use app\user\model\Certified as CertifiedModel;
use think\Db;
use service\Format;

/**
 * 会员认证控制器
 * Class Certified
 * @package app\member\admin
 * @author 似水星辰 [ 2630481389@qq.com ]
 * @since 2019/4/3 11:18
 */
class Certified extends Base
{

    /**
     * 实名认证
     * @author 似水星辰 [ 2630481389@qq.com ]
     * @return mixed
     * @throws \think\exception\DbException
     */
    public function realname()
    {
        cookie('__forward__', $_SERVER['REQUEST_URI']);

        // 实名认证
        $map['auth_type'] = 1;
        $map['status'] = 0;
        // 排序
        $order = $this->getOrder('status asc, id desc');

        // 数据列表
        $data_list = CertifiedModel::getList($map, $order);

        $fields = [
            ['id', 'ID'],
            ['name', ' 姓名'],
            ['idcard_front', '身份证正面', 'picture'],
            ['idcard_reverse', '身份证反面', 'picture'],
            ['idcard_no', '身份证号码'],
            ['user_id', '申请人', 'callback', 'get_nickname'],
            ['create_time', '申请时间'],
            ['reason', '失败（拒绝）原因', 'text'],
            ['status', '认证状态', 'status', '', ['待审核', '已通过', '已拒绝']],
            ['right_button', '操作', 'btn']
        ];
        return Format::ins()//实例化
        ->addColumns($fields)//设置字段
        ->setRightButton(['ident' => 'enable', 'title' => '审核', 'href' => ['enable', ['id' => '__id__', 'type' => 1]], 'icon' => 'fa fa-check pr5', 'class' => 'btn btn-xs mr5 btn-default btn-flat ajax-get confirm'])
            ->setRightButton(['ident' => 'disable', 'title' => '拒绝', 'href' => ['disable', ['id' => '__id__']], 'icon' => 'fa fa-close pr5', 'data-toggle' => 'prompt', 'class' => 'btn btn-xs mr5 btn-default btn-flat'])
            ->replaceRightButton(['status' => 1], '', 'enable')
            ->replaceRightButton(['status' => 2], '', ['enable', 'disable'])
            ->setData($data_list)//设置数据
            ->fetch();//显示
    }

    /**
     * 审核会员
     * @author 似水星辰 [ 2630481389@qq.com ]
     * @since 14:26
     * @param int $id 会员id
     * @return void
     * @throws \think\Exception
     * @throws \think\exception\PDOException
     */
    public function enable($id = 0, $type = 0)
    {

        if ($id == 0 || $type = 0) {
            $this->error('参数错误');
        }

        // 启动事务
        Db::startTrans();
        try {
            $info = CertifiedModel::where(['id' => $id, 'status' => 0])->find();
            $res = CertifiedModel::where(['id' => $id, 'status' => 0])->update(['status' => 1]);
            $res1 = \app\user\model\User::where(['id' => $info['user_id']])->update(['user_name' => $info['name'],'update_time'=>time()]);
            if(!$res || !$res1){
                $res3 = 0;
                exception('审核失败');
            }

            $res3 = 1;
            // 提交事务
            Db::commit();
        } catch (\Exception $e) {
            // 回滚事务
            Db::rollback();
            $this->error($e->getMessage());
        }

        if($res3){
            $data['to_user_id'] = $info['user_id'];
            $data['title'] = config('web_site_title')."友情提示";
            $data['content'] = "您的实名认证信息已通过";
            $data['type'] = 1;
            $data['template_type'] = 1;
            $msg = new SystemMessageModel();
            $ret = $msg->create($data);
            if (!$ret) {
                $this->error('创建消息失败');
            }

            $ret = $msg->sendMsg($data);
        }

        $this->success('审核通过');

    }

    public function disable($id = 0)
    {
        if ($id == 0) {
            $this->error('参数错误');
        }
        $msg = input('param.msg');

        $result = CertifiedModel::where(['id' => $id, 'status' => 0])->update(['status' => 2, 'reason' => $msg]);

        if ($result) {
            $this->success('拒绝成功！');
        }
    }

}
