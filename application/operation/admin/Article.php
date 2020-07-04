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
use app\operation\model\Article as ArticleModel;
use app\operation\model\ArticleColumn;
use service\Format;

/**
 * 文档控制器
 * Class Article
 * @package app\cms\admin
 * @author 似水星辰 [ 2630481389@qq.com ]
 * @since 2019/4/4 11:44
 */
class Article extends Base
{

    /**
     * 文档列表
     * @author 似水星辰 [ 2630481389@qq.com ]
     * @since 2019/4/4 11:46
     * @return mixed
     * @throws \think\exception\DbException
     */
    public function index()
    {
        cookie('__forward__', $_SERVER['REQUEST_URI']);
        // 查询
        $map = $this->getMap();
        $map[] = ['oa.trash', '=', 0];
        // 排序
        $order = $this->getOrder('sort asc, id desc');
        // 数据列表
        $data_list = ArticleModel::getList($map, $order);
        $fields =[
            ['id', 'ID'],
            ['title', '标题'],
            ['name', '栏目名称'],
            ['click_count', '点击量', 'text.edit'],
            ['add_time', '发布时间'],
            ['sort', '排序', 'text.edit'],
            ['status', '状态', 'status'],
            ['right_button', '操作', 'btn']
        ];
        return Format::ins()//实例化
        ->addColumns($fields)//设置字段
            ->setSearch(['title'=>'文章标题'])
            ->setOrder('click_count,title')
        ->setTopButtons($this->top_button)
            ->setRightButtons($this->right_button)
            ->setData($data_list)//设置数据
            ->fetch();//显示
    }

    /**
     * 添加文档
     * @param int $cid 栏目id
     * @param string $model 模型id
     * @author 似水星辰 [ 2630481389@qq.com ]
     * @return mixed
     * @throws \think\exception\PDOException
     */
    public function add($cid = 0)
    {
        // 保存文档数据
        if ($this->request->isAjax() || $this->request->isPost()) {

            $DocumentModel = new ArticleModel();
            if (false === $DocumentModel->saveData()) {
                $this->error($DocumentModel->getError());
            }
            $this->success('新增成功',"index");
        }
      
        $columns = ArticleColumn::getTreeList(0, false);
        $fields = [
            ['type'=>'hidden', 'name'=>'status','value'=>1],
            ['type'=>'hidden', 'name'=>'user_id','value'=>UID],
            ['type' => 'select', 'name' => 'category_id', 'title' => '所属分类','extra' => $columns],
            ['type' => 'text', 'name' => 'title', 'title' => '标题'],
            ['type' => 'image', 'name' => 'img_url', 'title' => '缩略图'],
            ['type' => 'number','name' => 'sort','title' => '排序', 'value' => 0],
            ['type' => 'number', 'name' => 'click_count', 'title' => '点击数', 'value' => 0],
            ['type' => 'radio', 'name' => 'is_recommend', 'title' => '是否推荐', 'extra'=>['否','是'], 'value' => 0],
            ['type' => 'textarea', 'name' => 'synopsis', 'title' => '文章简介'],
            ['type' => 'wangeditor', 'name' => 'body', 'title' => '详细内容'],
        ];
        $this->assign('page_title', '新增文章');
        $this->assign('form_items', $fields);
        return $this->fetch('admin@public/add');
    }

    /**
     * 编辑文档
     * @param null $id 文档id
     * @return mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @throws \think\exception\PDOException
     * @author 似水星辰 [2630481389@qq.com]
     */
    public function edit($id = null)
    {
        if ($id === null) $this->error('参数错误');

        // 保存文档数据
        if ($this->request->isPost()) {
            $DocumentModel = new ArticleModel();
            $result = $DocumentModel->saveData();
            if (false === $result) {
                $this->error($DocumentModel->getError());
            }
            $this->success('编辑成功', "index");
        }

        // 获取数据
        $info = ArticleModel::getOne($id);
        $columns = ArticleColumn::getTreeList(0, false);

        $fields = [
            ['type' => 'hidden', 'name' => 'id'],
            ['type' => 'select', 'name' => 'category_id', 'title' => '所属分类','extra' => $columns],
            ['type' => 'text', 'name' => 'title', 'title' => '标题'],
            ['type' => 'image', 'name' => 'img_url', 'title' => '缩略图'],
            ['type' => 'number','name' => 'sort','title' => '排序', 'value' => 0],
            ['type' => 'number', 'name' => 'click_count', 'title' => '阅读量', 'value' => 0],
            ['type' => 'radio', 'name' => 'is_recommend', 'title' => '是否推荐', 'extra'=>['否','是']],
            ['type' => 'textarea', 'name' => 'synopsis', 'title' => '文章简介'],
            ['type' => 'wangeditor', 'name' => 'body', 'title' => '详细内容'],
        ];
            $this->assign('page_title','编辑文章接口');
            $this->assign('form_items',$this->setData($fields, $info));
            return $this->fetch('admin@public/edit');

    }

    /**
     * 删除文档(不是彻底删除，而是移动到回收站)
     * @param null $ids 文档id
     * @param string $table 数据表
     * @author 似水星辰 [2630481389@qq.com]
     * @return mixed
     */
    public function delete($ids = null)
    {
        if ($ids === null) $this->error('参数错误');   
        $ret = \app\operation\model\Article::where('id', 'in', $ids)->setField('trash', 1);
        // 移动文档到回收站
        if (false === $ret) {
            $this->error('删除失败');
        }
        return $this->success('删除成功');
    }
}