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

namespace app\operation\model;

use think\Db;
use think\Model as ThinkModel;

/**
 * 文档模型
 * Class Document
 * @package app\operation\model
 * @author 似水星辰 [ 2630481389@qq.com ]
 * @since 2019/4/4 11:47
 */
class Article extends ThinkModel
{
    // 设置当前模型对应的完整数据表名称
    protected $table = '__OPERATION_ARTICLE__';
    //附表
    const EXTRA_TABLE = "operation_article_body";

    // 自动写入时间戳
    protected $autoWriteTimestamp = true;

    /**
     * 获取文档列表
     * @param array $map 筛选条件
     * @param array $order 排序
     * @return mixed
     * @throws \think\exception\DbException
     * @author 似水星辰 [ 2630481389@qq.com ]
     */
    public static function getList($map = [], $order = [])
    {
        $data_list = self::view('operation_article oa', 'id,category_id,title,img_url,synopsis,click_count,fabulous,is_recommend,create_time as add_time,update_time,status,sort')
            ->view("operation_article_column oac", 'name,cat_img', 'oac.id=oa.category_id', 'left')
            ->where($map)
            ->order($order)
            ->paginate()->each(function($item){
                $item['title'] = str2sub($item['title'],16);
                $item['synopsis'] = str2sub($item['synopsis'],58);
                $item['img_url'] = get_file_url($item['img_url']);
                $item['cat_img'] = get_file_url($item['cat_img']);
                $item['add_time'] = date('Y-m-d H:i:s',$item['add_time']);
                return $item;
            });
        return $data_list;
    }

    /**
     * 获取单篇文档
     * @param string $id 文档id
     * @param array $map 查询条件
     * @return mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @author 似水星辰 [ 2630481389@qq.com ]
     */
    public static function getOne($id = '', $map = [])
    {
        $data = self::view('operation_article', 'id,category_id,title,img_url,synopsis,click_count,fabulous,is_recommend,create_time as add_time,update_time,status,sort');
        if (self::EXTRA_TABLE != '') {
            $data = $data->view(self::EXTRA_TABLE, true, 'operation_article.id=' . self::EXTRA_TABLE . '.aid', 'left');
        }
        return $data->view("operation_article_column", 'name', 'operation_article_column.id=operation_article.category_id', 'left')
            ->where('operation_article.id', $id)
            ->where($map)
            ->find();
    }

    /**
     * 新增或更新文档
     * @return bool
     * @throws \think\exception\PDOException
     * @author 似水星辰 [ 2630481389@qq.com ]
     */
    public function saveData()
    {
        $data = request()->post();

        $data['user_id'] = UID;

        self::startTrans();
        try {
            if ($data['id']) {
                $ret = $this->where("id", $data['id'])->update($data);
                if (false === $ret) {
                    exception('编辑主表失败');
                }

                $ret_body = Db::name(self::EXTRA_TABLE)->where("aid", $data['id'])->update(['body' => $data['body']]);

                if (false === $ret_body) {
                    exception('编辑附加表失败');
                }
            } else {
                if(!$data['title']){
                    exception('标题不能为空');
                }
                $ret = $this->create($data);
                if (false === $ret) {
                    exception('新增主表记录失败');
                }
                $ret_body = Db::name(self::EXTRA_TABLE)->insert(['aid' => $ret->id, 'body' => $data['body']]);
                if (false === $ret_body) {
                    exception('新增附加表记录失败');
                }
            }

            // 提交事务
            self::commit();
        } catch (\Exception $e) {
            // 回滚事务
            self::rollback();
            $this->error = $e->getMessage();
            return false;
        }
        return true;
    }

    /**
     * 删除
     * @param type $ids
     * @return boolean
     * @throws \think\Exception
     * @throws \think\exception\PDOException
     * @author 似水星辰 [ 2630481389@qq.com ]
     */
    public static function delIds($ids)
    {
        self::where("id", "in", $ids)->delete();
        Db::name(self::EXTRA_TABLE)->where("aid", "in", $ids)->delete();
        return true;
    }
}