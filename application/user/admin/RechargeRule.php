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
use app\user\model\RechargeRule as RechargeRuleModel;
use service\Format;

/**
 * 充值规则管理
 * Class RechargeRule
 * @package app\user\admin
 * @author 似水星辰 [ 2630481389@qq.com ]
 * @created 2019/5/8 15:57
 */
class RechargeRule extends Base
{

    /**
     * 充值规则列表
     * @author 似水星辰 [ 2630481389@qq.com ]
     * @return mixed
     */
    public function index($group = 0)
    {
        cookie('__forward__', $_SERVER['REQUEST_URI']);
        // 查询
        $map = $this->getMap();// 排序
//        $map['group'] = $map['group'] ? $map['group'] : $group;
        $order = $this->getOrder('sort asc,id DESC');
        if ($group == 1) {
            $fields = [// 批量添加数据列
                ['id', 'ID'],
                ['name', ' 规则名称'],
                ['app_name', '内购项目'],
                ['money', '支付价格',],
                ['add_money', '充值金额', 'text'],
                ['sort', '排序', 'text'],
                ['create_time', '创建时间'],
                ['status', '状态', 'status'],
                ['right_button', '操作', 'btn']
            ];
            $rightButtons = ['disable', 'delete'];
        } else if ($group == 0) {
            $fields = [// 批量添加数据列
                ['id', 'ID'],
                ['name', ' 规则名称'],
                ['money', '支付价格', 'text'],
                ['add_money', '充值金额', 'text'],
                ['sort', '排序', 'text'],
                ['create_time', '创建时间'],
                ['status', '状态', 'status'],
                ['right_button', '操作', 'btn']
            ];
            $rightButtons = ['disable', 'delete'];
        }
        $dataList = RechargeRuleModel::getList($map, $order,$group);
        $tab_list = [
            ['title' => '一般', 'url' => url('index', ['group' => 0])],
            ['title' => 'IOS', 'url' => url('index', ['group' => 1])]
        ];
        $search = [
            ['type' => 'text', 'name' => 'name', 'title' => '真实姓名', 'tips' => '请输入真实姓名','value'=>$map['name']],
        ];
        return Format::ins()//实例化
        ->addColumns($fields)//设置字段
            ->setOrder(['money','create_time'])
        ->setTabNav($tab_list, $group)//设置TAB分组
        ->setTopButton(['title' => '新增', 'href' => ['add', ['layer'=>1,'group' => $group]], 'icon' => 'fa fa-plus pr5', 'class' => 'btn btn-sm mr5 btn-success btn-flat open_right'])
            ->setRightButtons($this->right_button, $rightButtons)
            ->setData($dataList)//设置数据
            ->fetch();//显示
    }

    /**
     * 新增充值规则
     * @author 似水星辰 [ 2630481389@qq.com ]
     * @created 2019/5/8 19:25
     * @editor XX [ xxx@qq.com ]
     * @updated 2019.03.30
     * @return mixed
     */
    public function add($group = 0)
    {
        // 保存文档数据
        if ($this->request->isAjax()) {
            $data = $this->request->post();
            $data['app_name'] = $data['app_name'] ?? '';
            // 验证
            $result = $this->validate($data, 'RechargeRule.add');
            if (true !== $result)
                $this->error($result);

            if ($res = RechargeRuleModel::create($data)) {
                $this->success('新增成功', 'index');
            } else {
                $this->error('新增失败');
            }
        }

        $fields = [
            ['type' => 'radio', 'name' => 'group', 'title' => '类型', 'extra'=>['一般','IOS'], 'value' => 0],
            ['type' => 'text', 'name' => 'name', 'title' => '规则名称', 'tips'=>'一般规则名称和充值金额是相同的，用作前台展示'],
            ['type' => 'text', 'name' => 'money', 'title' => '支付价格', 'tips'=>'实际需要支付的价格'],
            ['type' => 'text', 'name' => 'add_money', 'title' => '充值金额', 'tips'=>'一般规则名称和充值金额是相同的，用作前台展示'],
            //['type' => 'text', 'name' => 'add_money', 'title' => '赠送金额'],
        ];

        $this->assign('page_title', '新增充值规则');
        $this->assign('form_items', $fields);
        return $this->fetch('../../admin/view/public/add');
    }

    /**
     * 编辑充值规则
     * @param null $id 会员等级id
     * @author 似水星辰 [2630481389@qq.com]
     * @return mixed
     */
    public function edit($id = null)
    {
        if ($id === null) {
            $this->error('缺少参数');
        }

        // 保存数据
        if ($this->request->isPost()) {
            // 表单数据
            $data = $this->request->post();

            // 验证
            $result = $this->validate($data, 'RechargeRule.edit');
            if (true !== $result) $this->error($result);

            if (RechargeRuleModel::update($data)) {
                $this->success('编辑成功', cookie('__forward__'));
            } else {
                $this->error('编辑失败');
            }
        }

        $info = RechargeRuleModel::get($id);
        $fields = [
            ['type' => 'hidden', 'name' => 'id'],
            ['type' => 'radio', 'name' => 'group', 'title' => '类型', 'extra'=>['一般','IOS']],
            ['type' => 'text', 'name' => 'name', 'title' => '规则名称', 'tips'=>'一般规则名称和充值金额是相同的，用作前台展示'],
            ['type' => 'text', 'name' => 'money', 'title' => '支付价格', 'tips'=>'实际需要支付的价格'],
            ['type' => 'text', 'name' => 'add_money', 'title' => '充值金额', 'tips'=>'一般规则名称和充值金额是相同的，用作前台展示'],
            //['type' => 'text', 'name' => 'add_money', 'title' => '赠送金额'],

        ];
        $this->assign('page_title', '编辑充值规则');
        $this->assign('form_items', $this->setData($fields, $info));
        return $this->fetch('../../admin/view/public/edit');
    }
}
