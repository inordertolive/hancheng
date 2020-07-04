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
use app\operation\model\Suggestions as SuggestionsModel;
use app\operation\model\SuggestionsType;
use service\Format;

/**
 * 投诉建议控制器
 * @package app\Suggestions\admin
 */
class Suggestions extends Base
{
    /**
     * 投诉建议列表
     * @return mixed
     * @author 似水星辰 [2630481389@qq.com]
     */
    public function index()
    {
        cookie('__forward__', $_SERVER['REQUEST_URI']);
        // 查询
        $map = $this->getMap();
        // 排序
        $order = $this->getOrder();
        // 数据列表
        $data_list = SuggestionsModel::where($map)->order($order)->paginate();
        $type = SuggestionsType::where('status',1)->column('id,title');
        $fields = [
            ['id', 'ID'],
            ['type', '投诉建议类型名称','status','',$type],
            ['body', '内容'],
            ['right_button', '操作', 'btn', '', '', 'text-center']
        ];
        return Format::ins()//实例化
        ->addColumns($fields)//设置字段
        ->setTopButtons($this->top_button, ['add', 'disable','enable'])
        ->setRightButtons($this->right_button, ['edit', 'disable'])
            ->setData($data_list)//设置数据
            ->fetch();//显示
    }

    /**
     * 投诉建议类型
     * @return mixed
     * @author 似水星辰 [2630481389@qq.com]
     */
    public function type()
    {
        cookie('__forward__', $_SERVER['REQUEST_URI']);
        // 查询
        $map = $this->getMap();
        // 排序
        $order = $this->getOrder();
        // 数据列表
        $data_list = SuggestionsType::where($map)->order($order)->paginate();
        $fields = [
            ['id', 'ID'],
            ['title', '名称'],
            ['right_button', '操作', 'btn', '', '', 'text-center']
        ];
        return Format::ins()//实例化
        ->addColumns($fields)//设置字段
        ->setTopButtons($this->top_button,['disable','enable','delete'])
            ->setRightButtons($this->right_button,['disable','delete'])
            ->setRightButton(['ident' => 'enable', 'title' => '启用', 'href' => ['type_status', ['ids' => '__id__', 'status' => 1]], 'icon' => 'fa fa-check-circle pr5', 'class' => 'btn btn-xs mr5 btn-default btn-flat ajax-get confirm'])
            ->setRightButton(['ident' => 'disable', 'title' => '禁用', 'href' => ['type_status', ['ids' => '__id__','status' => 0]], 'icon' => 'fa fa-ban pr5', 'class' => 'btn btn-xs mr5 btn-default btn-flat  ajax-get confirm'])
            ->setRightButton(['ident' => 'delete', 'title' => '删除', 'href' => ['type_status', ['ids' => '__id__', 'status' => 3]], 'icon' => 'fa fa-close pr5', 'class' => 'btn btn-xs mr5 btn-default btn-flat ajax-get confirm'])
            ->replaceRightButton(['status' => 0], '', 'disable')
            ->replaceRightButton(['status' => 1], '', 'enable')
            ->setData($data_list)//设置数据
            ->fetch();//显示
    }

    /**
     * 新增
     * @return mixed
     * @author 似水星辰 [2630481389@qq.com]
     */
    public function add()
    {
        // 保存数据
        if ($this->request->isPost()) {
            // 表单数据
            $data = $this->request->post();

            // 验证
            $result = $this->validate($data, 'Suggestions.typeadd');
            if (true !== $result) $this->error($result);

            if ($page = SuggestionsType::create($data)) {
                $this->success('新增成功', cookie('__forward__'));
            } else {
                $this->error('新增失败');
            }
        }

        $fields = [
            ['type' => 'text', 'name' => 'title', 'title' => '名称', 'tips' => '', 'attr' => '', 'value' => ''],

        ];
        $this->assign('page_title', '新增投诉建议类型');
        $this->assign('form_items', $fields);
        return $this->fetch('../../admin/view/public/add');
    }

    /**
     * 编辑
     * @param null $id 投诉建议id
     * @return mixed
     * @author 似水星辰 [2630481389@qq.com]
     */
    public function edit($id = null)
    {
        if ($id === null) $this->error('缺少参数');

        // 保存数据
        if ($this->request->isPost()) {
            // 表单数据
            $data = $this->request->post();

            // 验证
            $result = $this->validate($data, 'Suggestions.typeadd');
            if (true !== $result) $this->error($result);

            if (SuggestionsType::update($data)) {
                $this->success('编辑成功', cookie('__forward__'));
            } else {
                $this->error('编辑失败');
            }
        }

        $info = SuggestionsType::get($id);
        $fields = [
            ['type' => 'hidden', 'name' => 'id'],
            ['type' => 'text', 'name' => 'title', 'title' => '名称', 'tips' => '', 'attr' => '', 'value' => ''],
        ];
        $this->assign('page_title', '编辑投诉建议类型');
        $this->assign('form_items', $this->setData($fields, $info));
        return $this->fetch('../../admin/view/public/edit');
    }

    /**
     * 禁用/启用/删除  投诉建议类型
     * @return mixed
     * @author 似水星辰 [2630481389@qq.com]
     */
    public function type_status($ids,$status)
    {
        switch ($status) {
            case 0: // 禁用
                $result = SuggestionsType::where('id','IN',$ids)->setField('status', 0);
                break;
            case 1: // 启用
                $result = SuggestionsType::where('id','IN',$ids)->setField('status', 1);
                break;
            case 3: // 启用
                $result = SuggestionsType::where('id','IN',$ids)->delete();
                break;
            default:
                $this->error('非法操作');
                break;
        }
        if($result){
            $this->success('操作成功');
        }
    }
}