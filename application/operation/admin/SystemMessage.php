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
namespace app\operation\admin;

use app\admin\admin\Base;
use app\operation\model\SystemMessage as SystemMessageModel;
use service\Format;

/**
 * 站内信
 * Class SystemMessage
 * @package app\operation\admin
 * @author 似水星辰 [ 2630481389@qq.com ]
 * @created 2019/4/28 11:02
 */
class SystemMessage extends Base
{
    /**
     * 任务列表
     * @author 似水星辰 [ 2630481389@qq.com ]
     */
    public function index()
    {
        cookie('__forward__', $_SERVER['REQUEST_URI']);
        // 查询
        $map = $this->getMap();        // 排序
        $order = $this->getOrder('id DESC');
        $dataList = SystemMessageModel::where($map)->order($order)->paginate();
        $fields = [
            ['id', '序号'],
            ['to_user_id', '接收人'],
            ['title', '消息标题'],
            ['content', '消息内容'],
            ['is_read', '是否阅读', 'text', '', [0 => '未读', 1 => '已读']],
            ['create_time', '创建时间'],
            ['right_button', '操作', 'btn']
        ];
        return Format::ins()//实例化
        ->addColumns($fields)//设置字段
        ->setTopButtons($this->top_button, ['disable', 'enable'])
            ->setRightButton(['ident' => 'rest', 'title' => '重新发送', 'href' => ['rest', ['id' => '__id__']], 'icon' => 'fa fa-refresh pr5', 'class' => 'btn btn-xs mr5 btn-default btn-flat ajax-get confirm'])
            ->setRightButtons($this->right_button, ['edit', 'disable'])
            ->setData($dataList)//设置数据
            ->fetch();//显示
    }

    public function add()
    {
        // 保存数据
        if ($this->request->isPost()) {
            $data = request()->post();
            if ($data['type'] < 3 && !$data['to_user_id']) {
                $this->error('请填写收信人ID');
            }

            if (!$data['title']) {
                $this->error('请填写消息标题');
            }
            if (!$data['content']) {
                $this->error('请填写消息内容');
            }
            $msg = new SystemMessageModel();
            $ret = $msg->create($data);
            if (!$ret) {
                $this->error('创建消息失败');
            }

            $ret = $msg->sendMsg($data);

            if (true === $ret) {
                $this->success('发送成功','index');
            }
            $this->error($msg->getError());
        }

        $fields = [
            ['type' => 'radio', 'name' => 'type', 'title' => '发送类型', 'tips' => '', 'extra' => [1 => '单推', 2 => '多推', 3 => '全体推'], 'value' => 1],
            ['type' => 'radio', 'name' => 'template_type', 'title' => '消息功能', 'tips' => '必填', 'extra' => [1 => '通知透传打开app', 2 => '打开网页链接功能', 3 => '透传功能(ios)', 4 => '通知弹框下载'], 'value' => 1],
            ['type' => 'text', 'name' => 'to_user_id', 'title' => '接收人会员ID', 'tips' => '单推多推时必填，多推请用逗号分隔'],
            ['type' => 'text', 'name' => 'title', 'title' => '消息标题', 'tips' => '必填，50个字以内'],
            ['type' => 'textarea', 'name' => 'content', 'title' => '消息内容', 'tips' => '50个字以内'],
            ['type' => 'text', 'name' => 'link', 'title' => '要打开的链接', 'tips' => '打开网页链接功能有效'],
            ['type' => 'text', 'name' => 'pop_title', 'title' => '弹窗标题', 'tips' => '弹窗下载类型必须填写，50个字以内'],
            ['type' => 'textarea', 'name' => 'pop_content', 'title' => '弹窗内容', 'tips' => '弹窗下载类型必须填写,50个字以内'],
            ['type' => 'text', 'name' => 'download', 'title' => '下载地址', 'tips' => '弹窗下载类型必须填写'],
        ];

        $this->assign('page_title', '新增站内信');
        $this->assign('form_items', $fields);
        return $this->fetch('admin@public/add');
    }

    /**
     * 重新发送
     * @param $id
     * @return void
     * @author 似水星辰 [ 2630481389@qq.com ]
     * @created 2019/8/29 15:04
     * @editor XX [ xxx@qq.com ]
     * @updated 2019.03.30
     */
    public function rest($id){
        $msg = new SystemMessageModel();
        $data = $msg->get($id);
        $ret = $msg->sendMsg($data);

        if (true === $ret) {
            $this->success('发送成功','index');
        }
        $this->error($msg->getError());
    }
}