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
use app\operation\model\Ads as AdsModel;
use app\operation\model\Nav as NavModel;
use app\operation\model\Suggestions;
use app\operation\model\SuggestionsType;
use app\operation\model\ArticleColumn;
use app\operation\model\Article;
use app\operation\model\SystemMessage;
use app\operation\model\SystemMessageRead;
use service\ApiReturn;
use think\Db;

/**
 * 运营广告接口
 * Class Ads
 * @package app\api\controller\v1
 */
class Operation extends Base
{

    /**
     * 获取指定广告位的广告列表
     * @param array $data 参数
     * @param array $user
     * @return json
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @author 似水星辰 [ 2630481389@qq.com ]
     */
    public function get_ads($data = [], $user = [])
    {
        $result = AdsModel::where(['typeid' => $data['typeid'], 'status' => 1])->select()->toArray();
        if (count($result) >= 1) {
            foreach ($result as &$v) {
                if ($v['thumb']) {
                    $v['thumb'] = get_file_url($v['thumb']);
                }
                $v = $this->filter($v, $this->fname);
            }
            return ApiReturn::r(1, $result, '请求成功');
        }
        return ApiReturn::r(1, [], '暂无数据');
    }

    /**
     * 获取指定导航位的导航列表
     * @param string $data 参数
     * @return json
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @author 似水星辰 [ 2630481389@qq.com ]
     */
    public function get_nav($data = '')
    {
        $result = NavModel::where(['typeid' => $data['typeid'], 'status' => 1])->select()->toArray();
        if (count($result) >= 1) {
            foreach ($result as &$v) {
                if ($v['thumb']) {
                    $v['thumb'] = get_file_url($v['thumb']);
                }
                $v = $this->filter($v, $this->fname);
            }
            return ApiReturn::r(1, $result, '请求成功');
        }
        return ApiReturn::r(1, [], '暂无数据');
    }

    /**
     * 投诉建议列表
     * @param array $data
     * @param array $user
     * @return \think\response\Json
     * @throws \think\exception\DbException
     * @since 2019/4/20 17:28
     * @author 似水星辰 [ 2630481389@qq.com ]
     */
    public function suggestions($data = [], $user = [])
    {
        $result = Suggestions::where('user_id', $user['id'])->field('id,type,body')->paginate();
        if ($result) {
            return ApiReturn::r(1, $result, '请求成功');
        }
        return ApiReturn::r(1, [], '暂无数据');
    }

    /**
     * 投诉建议类型列表
     * @param array $data
     * @param array $user
     * @return \think\response\Json
     * @throws \think\exception\DbException
     * @since 2019/4/20 17:28
     * @author 似水星辰 [ 2630481389@qq.com ]
     */
    public function suggestions_type($data = [], $user = [])
    {
        $result = SuggestionsType::where('status', 1)->field('id,title')->select();
        if (count($result) >= 1) {
            foreach ($result as &$v) {
                $v = $this->filter($v, $this->fname);
            }
            return ApiReturn::r(1, $result, '请求成功');
        }
        return ApiReturn::r(1, [], '暂无数据');
    }

    /**
     * 添加投诉建议
     * @param array $data
     * @param array $user
     * @return \think\response\Json
     * @since 2019/4/20 17:28
     * @author 似水星辰 [ 2630481389@qq.com ]
     */
    public function add_suggestions($data = [], $user = [])
    {
        $data['user_id'] = $user['id'] ? $user['id'] : 0;

        $result = Suggestions::create($data);
        if ($result) {
            return ApiReturn::r(1, [], '提交成功');
        }
    }

    /**
     * 获取指定的单页分类信息
     * @param array $data
     * @return void
     * @author 似水星辰 [ 2630481389@qq.com ]
     * @created 2019/8/26 19:12
     * @editor XX [ xxx@qq.com ]
     * @updated 2019.03.30
     */
    public function get_column($data = [])
    {
        $result = ArticleColumn::getInfo($data['category_id']);
        if ($result) {
            $result['cat_img'] = get_file_url($result['cat_img']);
            $result = $this->filter($result, $this->fname);
            return ApiReturn::r(1, $result, '请求成功');
        }
    }

    /**
     * 获取指定栏目的文章列表
     * @param array $data
     * @return void
     * @author 似水星辰 [ 2630481389@qq.com ]
     * @created 2019/8/26 19:12
     */
    public function get_column_article_list($data = [])
    {
        $map[]=['category_id','=',$data['category_id']];
        $map[]=['title','like','%'.$data['keyword'].'%'];
        $result = Article::getList($map);
        if ($result) {
            return ApiReturn::r(1, $result, '请求成功');
        }
    }

    /**
     * 获取指定的单页分类信息
     * @param array $data
     * @return void
     * @author 似水星辰 [ 2630481389@qq.com ]
     * @created 2019/8/26 19:12
     * @editor XX [ xxx@qq.com ]
     * @updated 2019.03.30
     */
    public function get_article_detail($data = [])
    {
        $result = Article::getOne($data['id']);
        if ($result) {
            $result['img_url'] = get_file_url($result['img_url']);
            $result['add_time'] = date('Y-m-d H:i:s', $result['add_time']);
            Article::where('id', $data['id'])->setInc('click_count');
            return ApiReturn::r(1, $result, '请求成功');
        }
    }

    /**
     * 获取站内信类型列表
     * @param $data
     * @param $user
     * @return \think\response\Json
     * @since 2019/4/9 16:16
     * @author 似水星辰 [ 2630481389@qq.com ]
     */
    function getSystemMsgType($data, $user)
    {
        if (!$user) {
            return ApiReturn::r(0, '', '请开启USER授权');
        }
        $user_id = $user['id'];
        $types = SystemMessage::$msgtype;
        $info = [];
        foreach ($types as $id => $name) {
            $row['msg_type'] = $id;
            $row['name'] = $name;
            $row['new_msg'] = [];
            $new = SystemMessage::getNew($user_id, $id);
            if ($new) {
                $row['new_msg'] = $this->filter($new, $this->fname);
            }
            $info[] = $row;
        }
        return ApiReturn::r(1, $info, '请求成功');
    }

    /**
     * 获取指定类型的消息列表
     * @param $data
     * @param $user
     * @return \think\response\Json
     * @since 2019/4/9 16:17
     * @author 似水星辰 [ 2630481389@qq.com ]
     */
    function getSystemMsgList($data, $user)
    {
        $user_id = $user['id'];
        $msgtype = $data['msgtype'];
        $dataList = SystemMessage::getList($user_id, $msgtype);
        return ApiReturn::r(1, $dataList, '请求成功');
    }

    /**
     * 删除站内信
     * @param $data
     * @param $user
     * @return \think\response\Json
     * @since 2019/4/9 16:17
     * @author 似水星辰 [ 2630481389@qq.com ]
     */
    function delSystemMsg($data, $user)
    {
        if (!$user) {
            return ApiReturn::r(0, '', '请开启USER授权');
        }
        $user_id = $user['id'];
        // 启动事务
        Db::startTrans();
        try {
            $msg = SystemMessage::get($data['id']);
            if (!$msg) {
                $res = SystemMessageRead::delMsg($data['id']);
                if (!$res) {
                    return ApiReturn::r(1, [], '没有查询到此消息');
                }
            }
            if ($msg['type'] > 1) {
                $read = SystemMessageRead::getread($user_id, $data['id']);
                if (!$read) {
                    $read = SystemMessageRead::setread($user_id, $data['id']);
                }
                $res = SystemMessageRead::delread($read['aid']);

                if (!$res || !$read) {
                    exception('删除失败');
                }
            } else {
                if ($msg['to_user_id'] == $user['id']) {
                    $res = SystemMessage::where("id", $data['id'])->delete();
                    $res1 = SystemMessageRead::delMsg($data['id']);
                    if (!$res || !$res1) {
                        exception('删除失败');
                    }
                }
            }
            // 提交事务
            Db::commit();
        } catch (\Exception $e) {
            // 回滚事务
            Db::rollback();
            return ApiReturn::r(0, [], $e->getMessage());
        }

        return ApiReturn::r(1, [], '删除成功');
    }

    /**
     * 获取指定客服的详情
     * @author 似水星辰 [ 2630481389@qq.com ]
     * @created 2019/9/25 18:49
     */
    public function customer_detail($data, $user)
    {
        $info = \app\operation\model\Service::where('id', $data['id'])->field('password', true)->find();
        if ($info) {
            $info['avatar'] = get_file_url($info['avatar']);
            return ApiReturn::r(1, $this->filter($info, $this->fname), '请求成功');
        }
        return ApiReturn::r(1, [], '没有此客服');
    }
}
