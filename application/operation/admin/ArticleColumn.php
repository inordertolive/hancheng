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
use app\operation\model\ArticleColumn as ColumnModel;
use app\operation\model\Article;
use service\Tree;
use service\Format;


/**
 * 栏目控制器
 * Class Column
 * @package app\cms\admin
 * @author 似水星辰 [ 2630481389@qq.com ]
 * @since 2019/4/4 14:20
 */
class ArticleColumn extends Base
{
    /**
     * 栏目列表
     * @author 似水星辰 [ 2630481389@qq.com ]
     * @return mixed
     */
    public function index()
    {
        // 查询
        $map = $this->getMap();

        // 数据列表
        $data_list = ColumnModel::where($map)->column(true);
        if (empty($map)) {
            $data_list = Tree::config(['title' => 'name'])->toList($data_list);
        }

        $fields = [
            ['id', 'ID'],
            ['name', '栏目名称', 'callback', function ($value, $data) {
                return isset($data['title_prefix']) ? $data['title_display'] : $value;
            }, '__data__'],
            ['thumb', '栏目图', 'picture'],
            ['hide', '是否隐藏', 'status', '', ['否', '是']],
            ['create_time', '创建时间', 'datetime'],
            ['sort', '排序', 'text.edit'],
            ['status', '状态', 'status', '', ['禁用', '正常']],
            ['right_button', '操作', 'btn']
        ];

        return Format::ins()//实例化
        ->addColumns($fields)//设置字段
        ->setTopButton(['title' => '新增顶级栏目', 'href' => ['add', ['pid' => '0']], 'icon' => 'fa fa-plus pr5', 'class' => 'btn btn-sm mr5 btn-success btn-flat'])
            ->setRightButton(['title' => '新增子栏目', 'href' => ['add', ['pid' => '__id__']], 'icon' => 'fa fa-plus pr5', 'class' => 'btn btn-xs mr5 btn-success btn-flat'])
            ->setRightButtons($this->right_button)
            ->setData($data_list)//设置数据
            ->fetch();//显示
    }

    /**
     * 新增栏目
     * @param int $pid 父级id
     * @author 似水星辰 [ 2630481389@qq.com ]
     * @return mixed
     */
    public function add($pid = 0)
    {
        // 保存数据
        if ($this->request->isPost()) {
            // 表单数据
            $data = $this->request->post();

            // 验证
            $result = $this->validate($data, 'Column');

            if (true !== $result) $this->error($result);

            if ($column = ColumnModel::create($data)) {
                cache('cms_column_list', null);
                // 记录行为
                action_log('column_add', 'cms_column', $column['id'], UID, $data['name']);
                $this->success('新增成功', 'index');
            } else {
                $this->error('新增失败');
            }
        }

        $fields = [
            ['type' => 'select', 'name' => 'pid', 'title' => '所属栏目', 'tips' => '必选', 'extra' => ColumnModel::getTreeList(), 'value' => $pid],
            ['type' => 'text', 'name' => 'name', 'title' => '栏目名称', 'tips' => '必填'],
            ['type' => 'image', 'name' => 'cat_img', 'title' => '栏目图', 'tips' => '请上传图片'],
            ['type' => 'radio', 'name' => 'type', 'title' => '栏目类型', '', 'extra' => ['最终列表栏目', '单页'], 'value' => 0],
            ['type' => 'radio', 'name' => 'hide', 'title' => '是否隐藏栏目', 'tips' => '隐藏后前台不可见', 'extra' => ['显示', '隐藏'], 'value' => 0],
            ['type' => 'radio', 'name' => 'status', 'title' => '立即启用', 'extra' => ['否', '是'], 'value'=>1],
            ['type' => 'text', 'name' => 'sort', 'title' => '排序',  'value'=>100],
            ['type' => 'wangeditor', 'name' => 'content', 'title' => '栏目内容', 'tips' => '可作为单页使用'],
        ];
        $this->assign('page_title', '新增文章分类');
        $this->assign('form_items', $fields);
        return $this->fetch('admin@public/add');
    }

    /**
     * 编辑栏目
     * @param string $id 栏目id
     * @author 似水星辰 [ 2630481389@qq.com ]
     */
    public function edit($id = '')
    {
        if ($id === 0) $this->error('参数错误');

        // 保存数据
        if ($this->request->isPost()) {
            $data = $this->request->post();

            // 验证
            $result = $this->validate($data, 'Column');
            // 验证失败 输出错误信息
            if (true !== $result) $this->error($result);

            if (ColumnModel::update($data)) {
                // 记录行为
                action_log('column_edit', 'cms_column', $id, UID, $data['name']);
                return $this->success('编辑成功', 'index');
            } else {
                return $this->error('编辑失败');
            }
        }

        // 获取数据
        $info = ColumnModel::get($id);

        $fields = [
            ['type'=>'hidden', 'name'=>'id'],
            ['type' => 'select', 'name' => 'pid', 'title' => '所属栏目', 'tips' => '必选', 'extra' => ColumnModel::getTreeList()],
            ['type' => 'text', 'name' => 'name', 'title' => '栏目名称', 'tips' => '必填'],
            ['type' => 'image', 'name' => 'cat_img', 'title' => '栏目图', 'tips' => '请上传图片'],
            ['type' => 'radio', 'name' => 'type', 'title' => '栏目类型', '', 'extra' => ['最终列表栏目', '单页']],
            ['type' => 'radio', 'name' => 'hide', 'title' => '是否隐藏栏目', 'tips' => '隐藏后前台不可见', 'extra' => ['显示', '隐藏']],
            ['type' => 'radio', 'name' => 'status', 'title' => '立即启用', 'extra' => ['否', '是']],
            ['type' => 'text', 'name' => 'sort', 'title' => '排序', '', 100],
            ['type' => 'wangeditor', 'name' => 'content', 'title' => '栏目内容', '可作为单页使用'],
        ];
        $this->assign('page_title', '编辑文章分类');
        $this->assign('form_items', $this->setData($fields, $info));
        return $this->fetch('admin@public/edit');
    }

    /**
     * 删除栏目
     * @param null $ids 栏目id
     * @return mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @author 似水星辰 [ 2630481389@qq.com ]
     */
    public function delete($ids = null)
    {
        if ($ids === null) $this->error('参数错误');

        // 检查是否有子栏目
        if (ColumnModel::where('pid', $ids)->find()) {
            $this->error('请先删除或移动该栏目下的子栏目');
        }

        // 检查是否有文档
        if (Article::where('category_id', $ids)->find()) {
            $this->error('请先删除或移动该栏目下的所有文档');
        }

        // 检查是否有子栏目
        if (ColumnModel::where('id','in', $ids)->delete()) {
            $this->success('删除成功');
        }else{
            $this->success('删除失败');
        }

    }
}