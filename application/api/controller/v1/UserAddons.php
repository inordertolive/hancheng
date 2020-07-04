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
use app\user\model\Certified;
use service\ApiReturn;
use think\Db;

/**
 * 用户扩展接口
 * @package app\api\controller\v1
 */
class UserAddons extends Base
{
    /**
     * 会员签到
     * @param array $data
     * @param array $user
     * @return \think\response\Json
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @throws \think\exception\PDOException
     * @author 似水星辰 [ 2630481389@qq.com ]
     * @since 2019/4/11 16:51
     */
    public function userSignin($data = [], $user = [])
    {
        //实例化签到模型
        $sign = new \app\user\model\Signin();
        $result = $sign->userSignin($user['id']);
        if (false === $result) {
            return ApiReturn::r(0, [], $sign->getError());
        }

        //签到成功
        if ($result['status'] == 1) {
            return ApiReturn::r(1, $this->filter($result, $this->fname), "已连续签到{$result['days']}天");
        }
        //重复签到
        if ($result['status'] == 2) {
            return ApiReturn::r(1, $this->filter($result, $this->fname), $result['msg']);
        }
    }

    /**
     * 获取签到信息
     * @author 似水星辰 [ 2630481389@qq.com ]
     * @created 2019/9/11 16:35
     */
    public function get_user_signin($data = [], $user = [])
    {
        $sign = new \app\user\model\Signin();
        //获取今日签到数据
        $todayData = $sign->todayData($user['id']);
        if (false === $todayData) {
            return ApiReturn::r(0, [], $sign->getError());
        }
        $result = $sign->getInsertData($user['id']);
        if ($todayData['is_sign'] == 0) {
            $result['days'] = $result['days'] - 1;//因为是组装的数据，如果没签到的话，就-1
        }
        $result['today'] = $todayData['is_sign'] ? $todayData['is_sign'] : 0;
        $result['score'] = \app\user\model\User::where('id', $user['id'])->value('score');
        $result = $this->filter($result, $this->fname);
        return ApiReturn::r(1, $result, '请求成功');
    }

    /**
     * 会员实名认证
     * @param array $data
     * @param array $user
     * @author 似水星辰 [ 2630481389@qq.com ]
     * @created 2019/9/1 19:36
     */
    public function certification($data = [], $user = [])
    {
        $data['user_id'] = $user['id'];
        // 验证
        $result = $this->validate($data, 'user/Certified.add');
        if (true !== $result) $this->error($result);
        // 启动事务
        Db::startTrans();
        try {
            //同一个用户，每种认证类型，只能有一条记录
            if($data['is_reset']){
                //如果是重新提交，则保存资料
                $data['update_time'] = time();
                $res = Certified::where(['user_id'=>$user['id'], 'auth_type'=>$data['auth_type']])->update($data);
                if (!$res) {
                    exception('提交认证材料失败');
                }
            }else{
                if(Certified::where(['user_id'=>$user['id'], 'auth_type'=>$data['auth_type']])->find()){
                    exception('认证中，请勿重复提交');
                }else{
                    $res = Certified::create($data);
                    if (!$res) {
                        exception('提交认证材料失败');
                    }
                }

            }
            Db::commit();
        } catch (\Exception $e) {
            // 更新失败 回滚事务
            Db::rollback();
            return ApiReturn::r(0, [], $e->getMessage());
        }

        return ApiReturn::r(1, [], '提交成功');
    }

    /**
     * 获取认证状态
     * @author 似水星辰 [ 2630481389@qq.com ]
     * @created 2019/9/1 20:22
     */
    public function certification_status($data = [], $user = [])
    {
        $res = Certified::where('user_id', $user['id'])->find();
        if ($res) {
            $result = $this->filter($res, $this->fname);
            return ApiReturn::r(1, $result, '请求成功');
        } else {
            return ApiReturn::r(1, ['status' => 99], '未认证');
        }
    }

    /**
     * 获取积分明细
     * @author 似水星辰 [ 2630481389@qq.com ]
     * @created 2019/9/9 10:38
     */
    public function get_score_list($data = [], $user = [])
    {
        $result = \app\user\model\ScoreLog::getList($user['id']);

        if ($result) {
            return ApiReturn::r(1, $result, '请求成功');
        } else {
            return ApiReturn::r(1, [], '暂无数据');
        }
    }

    /**
     * 获取积分交易明细
     * @return void
     * @since 2019/4/23 18:30
     * @author 似水星辰 [ 2630481389@qq.com ]
     */
    public function get_score_detail($data = [], $user = [])
    {
        if ($data['date']) {
            $start_time = strtotime($data['date']);
            $end_time = strtotime('+1 day', $start_time);
            $whereTime = "create_time BETWEEN $start_time AND $end_time";
        }

        $data = db('user_integral_log')->where('user_id', $user['id'])->where($whereTime)->field('order_no', true)->order('aid', 'desc')->paginate();
        if ($data) {
            return ApiReturn::r(1, $data, '请求成功');
        }
        return ApiReturn::r(1, [], '请求成功');
    }

    /**
     * 上传背景图
     * @param array $data
     * @param array $user
     * @return \think\response\Json
     * @since 2019/4/26 14:33
     * @author zlf [2420541105@qq.com ]
     */
    public function user_background($data = [], $user = [])
    {
        if (!$data['background']) {
            return ApiReturn::r(0, [], '上传图片不能为空！');
        }
        $update['background'] = $data['background'];
        $update['updatetime'] = time();
        $result = db('user_info')->where('user_id', $user['id'])->update($update);
        if ($result) {
            cache("userinfo_" . $user['id'], null);
            return ApiReturn::r(1, [], '修改成功');
        }
        return ApiReturn::r(0, [], '修改失败');
    }

    /**
     * 关注/取关
     * @param $data
     * @param $user
     * @return \think\response\Json
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @author 似水星辰 [ 2630481389@qq.com ]
     * @since 2019/4/9 13:55
     */
    public function follow($data = [], $user = [])
    {
        $follow = new \app\user\model\Follow();
        $user_id = $data['user_id']; //我的ID
        $fans_id = $user['id']; //主播ID
        if ($user_id == $fans_id) {
            return ApiReturn::r(0, '', '你不能自己关注自己');
        }
        $res = $follow->isFollow($user_id, $fans_id);
        if ($res) {
            $ret = $follow->delFollow($user_id, $fans_id);
            if ($ret) {
                return ApiReturn::r(1, ['follow' => 0], '取消关注成功');
            }
        } else {
            $ret = $follow->saveFollow($user_id, $fans_id);
            if ($ret) {
                return ApiReturn::r(1, ['follow' => 1], '关注成功');
            }
        }
        return ApiReturn::r(0, [], '关注失败');
    }

    /**
     *
     * 关注 和 粉丝列表
     * @param  $data
     * @param  $user
     * @return \think\response\Json
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @author zhulongfei [ 242054105@qq.com ]
     * autograph  nickname avatar
     *
     */
    public function follow_attention($data = [], $user = [])
    {
        $type = $data["type"];
        //我的关注
        if ($type == 1) {
            $uid = $user['id'];
            $res = Db::name("user_follow")
                ->alias("f")
                ->field("f.id,u.head_img,u.user_nickname,f.create_time,f.fans_id,f.user_id,ui.autograph")
                ->join("user u", "f.user_id = u.id")
                ->join("user_info ui", "ui.user_id = u.id")
                ->where("f.fans_id", $uid)
                ->order("f.create_time desc")
                ->paginate()
                ->each(function ($item, $key) {
                    $item['is_follow'] = 1;
                    $item['head_img'] = get_file_url($item['head_img']);
                    return $item;
                });
        } elseif ($type == 2) {
            //我的粉丝
            $res = Db::name("user_follow")
                ->alias("f")
                ->field("f.id,u.head_img,u.user_nickname,f.create_time,f.fans_id,f.user_id,ui.autograph")
                ->join("user u", "f.fans_id = u.id")
                ->join("user_info ui", "ui.user_id = u.id")
                ->where("f.user_id", $user['id'])
                ->order("f.create_time desc")
                ->paginate()
                ->each(function ($item, $key) {
                    $item['is_follow'] = db('user_follow')->where(['user_id' => $item['fans_id'], 'fans_id' => $item['user_id']])->count();
                    $item['head_img'] = get_file_url($item['head_img']);
                    return $item;
                });
        }
        if ($res) {
            return ApiReturn::r(1, $res, '请求成功');
        }
        return ApiReturn::r(1, [], '没有更多数据了');
    }

    /**
     * 获取我的收藏
     * @author 似水星辰 [ 2630481389@qq.com ]
     * @created 2019/9/9 10:38
     */
    public function get_collection_list($data = [], $user = [])
    {
        $result = \app\user\model\Collection::where('user_id', $user['id'])->field('aid,collect_id,user_id,type,collect_title,collect_img,collect_price,collect_sales')->paginate();

        if ($result) {
            return ApiReturn::r(1, $result, '请求成功');
        } else {
            return ApiReturn::r(1, [], '暂无数据');
        }
    }

    /**
     * 添加/取消收藏（本接口适用于商品详情页，文章内容页等场景）
     * @param array $data
     * @param array $user
     * @author 似水星辰 [ 2630481389@qq.com ]
     * @created 2019/9/24 18:55
     */
    public function set_collection($data = [], $user = [])
    {
        $collect = new \app\user\model\Collection();
        $res = $collect->isCollection($user['id'], $data['type'], $data['collect_id']);
        if ($res) {
            // 取消收藏
            $ret = $collect->delCollection($user['id'], $data['type'], $data['collect_id']);
            if ($ret) {
                return ApiReturn::r(1, ['is_collection' => 0], '取消收藏成功');
            }
        } else {
            $data['user_id'] = $user['id'];
            $ret = $collect->create($data);
            if ($ret) {
                return ApiReturn::r(1, ['is_collection' => 1], '收藏成功');
            }
        }
        return ApiReturn::r(0, [], '操作失败');
    }

    /**
     * 取消收藏（适用于个人中心-我的收藏-取消收藏）
     * @param array $data
     * @param array $user
     * @author 似水星辰 [ 2630481389@qq.com ]
     * @created 2019/9/24 21:44
     */
    public function cancel_collection($data = [], $user = [])
    {
        $collect = new \app\user\model\Collection();
        $res = $collect->where('aid', 'in', $data['aid'])->where('user_id', $user['id'])->delete();
        if ($res) {
            return ApiReturn::r(1, ['is_collection' => 0], '取消收藏成功');
        }
        return ApiReturn::r(1, [], '操作失败');
    }

    /**
     * 我邀请的人员列表
     * @author 似水星辰 [ 2630481389@qq.com ]
     * @created 2019/9/26 20:23
     */
    public function get_my_invite_user($data = [], $user = [])
    {
        $list = \app\user\model\User::where('lastid', $user['id'])->field('user_nickname,create_time,head_img,id')->paginate()
            ->each(function ($item) {
                $item['is_consum'] = \app\common\model\Order::where(['user_id' => $item['id'], 'order_type' => 2])->count();
            });
        if ($list) {
            return ApiReturn::r(1, $list, '请求成功');
        }
        return ApiReturn::r(1, [], '暂无数据');
    }

    /**
     * 手动绑定关系
     * @author 似水星辰 [ 2630481389@qq.com ]
     * @created 2019/9/29 11:20
     */
    public function set_user_lastid($data = [], $user = [])
    {
        //先查询是否有推荐人
        $now_lastid = Db::name('user')->where('id', $user['id'])->value('lastid');
        if ($now_lastid) {
            return ApiReturn::r(0, [], '您已经有推荐人了，请勿重复绑定');
        }
        //查询邀请码所属的用户id
        $lastid = Db::name('user_info')->where('invite_code', $data['invite_code'])->value('user_id');
        if ($lastid) {
            $res = Db::name('user')->where('id', $user['id'])->update(['lastid' => $lastid]);
            if ($res) {
                return ApiReturn::r(1, [], '绑定成功');
            }
        } else {
            return ApiReturn::r(0, [], '未找到推荐人信息');
        }
        return ApiReturn::r(0, [], '绑定失败');
    }

    /**
     * 获取VIP列表
     * @author 似水星辰 [ 2630481389@qq.com ]
     * @created 2019/10/3 23:17
     */
    public function get_vip($data = [], $user = []){
        $result = \app\user\model\Vip::where('status', 1)->field('create_time,update_time,status', true)->select()->each(function ($item) {
            $item['thumb'] = get_file_url($item['thumb']);
            $item['interest'] = get_file_url($item['interest']);
            return $item;
        });

        if ($result) {
            return ApiReturn::r(1, $result, '请求成功');
        } else {
            return ApiReturn::r(1, [], '暂无数据');
        }
    }

    /**
     * 获取VIP详情
     * @author 似水星辰 [ 2630481389@qq.com ]
     * @created 2019/10/3 23:17
     */
    public function get_one_vip($data = [], $user = []){
        $result = \app\user\model\Vip::where('aid', $data['aid'])->field('create_time,update_time,status', true)->find();

        if ($result) {
            return ApiReturn::r(1, $result, '请求成功');
        } else {
            return ApiReturn::r(1, [], '暂无数据');
        }
    }

}
