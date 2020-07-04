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
namespace app\index\controller;

use app\admin\model\Apiprocess as ApiprocessModel;
use think\Controller;
use service\ApiReturn;

/**
 * Class Api文档
 * @package app\index\controller
 * @author 似水星辰 [ 2630481389@qq.com ]
 * @created 2019/9/7 11:26
 */
class Api extends Controller
{
    /**
     * 获取模块
     * @author 似水星辰 [ 2630481389@qq.com ]
     * @created 2019/9/7 15:58
     */
    public function index($hash = "")
    {
        $module = \app\admin\model\Module::column('id,name,title');
        $i = 0;
        foreach ($module as $k => $item) {
            $api[$k]['parent_id'] = $i;
            $api[$k]['folder_id'] = $item['id'];
            $api[$k]['name'] = $item['title'];
            $api[$k]['item'] = $this->get_group($item['id'], $item['name']);
            $api[$k]['list'] = db('admin_api_list')->where(['module' => $item['name'], 'group' => 0, 'isTest' => 1])->select();
        }

        if (!$hash) {
            $frist = db('admin_api_list')->limit(1)->field('id,hash')->find();
            $hash = $frist['hash'];
            $apiinfo = \app\common\model\Apilist::get(['hash' => $hash]);
        }else{
            $apiinfo = \app\common\model\Apilist::get(['hash' => $hash]);
            $prev = db('admin_api_list')->where('id','<',$apiinfo['id'])->where(['group'=>$apiinfo['group'], 'isTest' => 1])->value('hash');
            $next = db('admin_api_list')->where('id','>',$apiinfo['id'])->where(['group'=>$apiinfo['group'], 'isTest' => 1])->value('hash');
        }

        if (empty($hash) || empty($apiinfo)) {
            $this->error('接口错误');
        }
        $f_field = \app\common\model\ApiFields::order('sort asc')->all(['hash' => $hash, 'type' => 1]); //返回字段
        $q_field = \app\common\model\ApiFields::all(['hash' => $hash, 'type' => 0]); //请求字段
        $this->assign('f_field', $f_field);
        $this->assign('q_field', $q_field);
        $this->assign('data', $apiinfo);

        $this->assign('hash', $hash);
        $this->assign('api', $api);
        $this->assign('prev', $prev);
        $this->assign('next', $next);
        $this->assign('count', db('admin_api_list')->where('isTest' , 1)->count());
        return $this->fetch();
    }

    /**
     * 获取分组
     * @param $id
     * @param $module
     * @author 似水星辰 [ 2630481389@qq.com ]
     * @created 2019/9/7 15:58
     */
    public function get_group($id, $module)
    {
        $list = \app\admin\model\Apigroup::where('module', $module)->column('aid,name');
        foreach ($list as $k => $l) {
            $g[$k]['parent_id'] = $id;
            $g[$k]['folder_id'] = $k;
            $g[$k]['name'] = $l;
            $g[$k]['item'] = $this->get_api($k);
        }
        return $g;
    }

    /**
     * 获取接口列表
     * @param $aid
     * @author 似水星辰 [ 2630481389@qq.com ]
     * @created 2019/9/7 16:01
     */
    public function get_api($aid)
    {
        $list = db('admin_api_list')->where(['group' => $aid, 'isTest' => 1])->field('id as apiid, info as name,hash')->select();
        foreach ($list as $s => $a) {
            $list[$s]['sort'] = 0;
            $list[$s]['folder_id'] = $aid;
        }

        return $list;
    }

    /**
     * 错误码列表
     * @return mixed
     * @throws \think\db\exception\BindParamException
     * @throws \think\exception\PDOException
     * @author 似水星辰 [ 2630481389@qq.com ]
     */
    public function errorlist() {
        $errorlist = ApiReturn::$Code;
        $this->assign('errorlist', $errorlist);
        return $this->fetch();
    }

    /**
     * 用户字段
     * @return mixed
     * @throws \think\db\exception\BindParamException
     * @throws \think\exception\PDOException
     * @author 似水星辰 [ 2630481389@qq.com ]
     */
    public function userlist() {
        $userFields = ApiReturn::$userFields;
        $this->assign('userFields', $userFields);
        return $this->fetch();
    }

    /**
     * 验证签名
     * @author 似水星辰 [ 2630481389@qq.com ]
     * @created 2019/9/8 14:01
     */
    public function check() {
        $keyword = $_POST['keyword'] ?? '';
        $app_secret = $_POST['app_secret'] ?? '';
        $code = '';
        $arr = explode('&',$keyword);
        foreach($arr as $v){
            $key = explode('=',$v);
            $newarr[$key[0]] = $key[1];
        }
        $newarr['appsecret'] = $app_secret;
        ksort($newarr);
        $string = [];
        foreach($newarr as $key=>$val){
            $string[] = $key . '=' . $val;
        }
        if($keyword){
            $code = sha1(implode("&",$string));
        }
        $this->assign('keyword', $keyword);
        $this->assign('app_secret', $app_secret);
        $this->assign('code', $code);
        return $this->fetch(); // 渲染模板
    }

    /**
     * 模拟请求
     * @author 似水星辰 [ 2630481389@qq.com ]
     * @created 2019/9/8 14:01
     */
    public function request($hash = ""){
        $apiinfo = \app\common\model\Apilist::get(['hash' => $hash]);
        $q_field = \app\common\model\ApiFields::all(['hash' => $hash, 'type' => 0]); //请求字段
        $this->assign('q_field', $q_field);
        $this->assign('data', $apiinfo);
        return $this->fetch(); // 渲染模板
    }

    public function process($hash = ""){
        $list = ApiprocessModel::order('sort asc,aid asc')->select();
        foreach($list as $k => $v){
            $order = "FIND_IN_SET(id,'".$v['content']."')";
            $list[$k]['item'] = db('admin_api_list')->where('id','in',$v['content'])->field('id,hash,info')->orderRaw($order)->select();
        }

        if (!$hash) {
            $frist = db('admin_api_list')->limit(1)->field('id,hash')->find();
            $hash = $frist['hash'];
            $apiinfo = \app\common\model\Apilist::get(['hash' => $hash]);
        }else{
            $apiinfo = \app\common\model\Apilist::get(['hash' => $hash]);
            $prev = db('admin_api_list')->where('id','<',$apiinfo['id'])->where(['group'=>$apiinfo['group'], 'isTest' => 1])->value('hash');
            $next = db('admin_api_list')->where('id','>',$apiinfo['id'])->where(['group'=>$apiinfo['group'], 'isTest' => 1])->value('hash');
        }

        if (empty($hash) || empty($apiinfo)) {
            $this->error('接口错误');
        }
        $f_field = \app\common\model\ApiFields::order('sort asc')->all(['hash' => $hash, 'type' => 1]); //返回字段
        $q_field = \app\common\model\ApiFields::all(['hash' => $hash, 'type' => 0]); //请求字段
        $this->assign('f_field', $f_field);
        $this->assign('q_field', $q_field);
        $this->assign('data', $apiinfo);

        $this->assign('hash', $hash);

        $this->assign('list', $list);
        return $this->fetch(); // 渲染模板
    }
}
