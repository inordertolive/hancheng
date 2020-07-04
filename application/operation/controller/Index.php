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
namespace app\operation\controller;

/**
 * 后台默认控制器
 * @package app\admin\controller
 */
class Index extends Admin
{
    /**
     * 后台首页
     * @author 似水星辰 <2630481389@qq.com>
     * @return string
     */
    public function index()
    {
        // 客服信息
        $userInfo = session('operation_user_auth');

        $this->assign([
            'uinfo' => $userInfo,
            'word' => db('operation_service_words')->where('status',1)->select(),
            'groups' => db('operation_service_group')->where('status', 1)->select(),
        ]);
        return $this->fetch();
    }

    /**
     * 获取正在服务的客户列表
     * @return \think\response\Json
     * @author 似水星辰 [ 2630481389@qq.com ]
     * @created 2019/7/3 18:03
     * @editor XX [ xxx@qq.com ]
     * @updated 2019.03.30
     */
    public function getUserList()
    {
        if(request()->isAjax()){

            // 此处只查询过去 三个小时 内的未服务完的用户
            $userList = db('operation_service_log')->field('user_id as id,user_name as name,user_avatar as avatar,user_ip as ip')
                ->where('kf_id', session('operation_user_auth.uid'))
                ->where('create_time', '>', time() - 3600 * 3)
                ->where('end_time', 0)
                ->group('id')
                ->select();

            return json(['code' => 1, 'data' => $userList, 'msg' => 'ok']);
        }
    }

    // 获取聊天记录
    public function getChatLog()
    {
        if(request()->isAjax()){

            $param = input('param.');

            $limit = 20; // 一次显示10 条聊天记录
            $offset = ($param['page'] - 1) * $limit;

            $logs = db('operation_service_chat')->where(function($query) use($param){
                $query->where('from_id', $param['uid'])->where('to_id', 'KF' . session('operation_user_auth.uid'));
            })->whereOr(function($query) use($param){
                $query->where('from_id', 'KF' . session('operation_user_auth.uid'))->where('to_id', $param['uid']);
            })->limit($offset, $limit)->order('aid', 'desc')->select();

            $total =  db('operation_service_chat')->where(function($query) use($param){
                $query->where('from_id', $param['uid'])->where('to_id', 'KF' .session('operation_user_auth.uid'));
            })->whereOr(function($query) use($param){
                $query->where('from_id', 'KF' . session('operation_user_auth.uid'))->where('to_id', $param['uid']);
            })->count();

            foreach($logs as $key=>$vo){

                $logs[$key]['type'] = 'user';
                $logs[$key]['time_line'] = date('Y-m-d H:i:s', $vo['create_time']);

                if($vo['from_id'] == 'KF' . session('operation_user_auth.uid')){
                    $logs[$key]['type'] = 'mine';
                }
            }

            return json(['code' => 1, 'data' => $logs, 'msg' => intval($param['page']), 'total' => ceil($total / $limit)]);
        }
    }

    // ip 定位
    public function getCity()
    {
        $ip = input('param.ip');

        $ip2region = new \Ip2Region();
        $info = $ip2region->btreeSearch($ip);

        $city = explode('|', $info['region']);

        if(0 != $info['city_id']){
            return json(['code' => 1, 'data' => $city['2'] . $city['3'] . $city['4'], 'msg' => 'ok']);
        }else{

            return json(['code' => 1, 'data' => $city['0'], 'msg' => 'ok']);
        }
    }
}