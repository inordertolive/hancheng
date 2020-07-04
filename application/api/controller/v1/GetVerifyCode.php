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
// 验证码获取和验证接口

namespace app\api\controller\v1;

use app\api\controller\Base;
use service\ApiReturn;
use app\common\model\LogSms;

class GetVerifyCode extends Base {

    /**
     * 获取验证码
     * @author 似水星辰 [ 2630481389@qq.com ]
     * @param array $data 参数
     * @return json
     */
    public function get_code($data = [], $user = []) {
        if (!$data['mobile']) {
            return ApiReturn::r(0, [], '手机号错误');
        }
        //注册场景下判断手机号存不存在
        if ($data['type'] == 1){
            $mobile_count = db('user')->where('mobile',$data['mobile'])->count();
            if ($mobile_count){
                return ApiReturn::r(0, [], '手机号已存在，无法注册');
            }
        }

        //登录,忘记密码场景下判断手机号存不存在
        if ($data['type'] == 3 || $data['type'] == 2){
            $mobile_count = db('user')->where('mobile',$data['mobile'])->count();
            if (!$mobile_count){
                return ApiReturn::r(0, [], '此账号不存在，请注册后再登录！');
            }
        }
        //修改手机号，去除会员对应的手机号
        if($data['type'] == 4){
            if (!$data['user_id']) {
                return ApiReturn::r(0, [], '会员id查询失败');
            }
            $data['mobile'] = db('user')->where('id',$data['user_id'])->value('mobile');
            if (!$data['mobile']) {
                return ApiReturn::r(0, [], '手机号错误');
            }
        }

        if (!preg_match("/^1\d{10}$/", $data['mobile'])) {
            throw new \Exception('手机号码格式错误');
        }
        $LogSms = new LogSms();
        $count = $LogSms->getMobileTodayCount($data['mobile']);
        if($count === false){
            return ApiReturn::r(0, [], '验证码接收量已超上限，请明日再来');
        }

        $phoneVerify = rand(1000, 9999);
        try{
            $type = $data['type'] ? $data['type'] : 0;
//            if(addons_config('DySms.status') && addons_config('Huyi.status')){
//                return ApiReturn::r(0, [], '短信接口只能开启一个，请关闭多余的');
//            }
//
//            if(addons_config('DySms.status')){
                //阿里云短信，装了哪个插件就开哪个，同时只能开一个
                $result = addons_action('DySms/DySms/send', [$data['mobile'], ['code' => $phoneVerify, 'type' => $type], '验证码']);
//            }
//
//            if(addons_config('Huyi.status')){
//                //互亿短信，装了哪个插件就开哪个，同时只能开一个
//                $result = addons_action('Huyi/Huyi/send', [$data['mobile'], ['code' => $phoneVerify, 'type' => $type], '验证码']);
//            }
        }
        catch(\Exception $e){
            return ApiReturn::r(0, [], '短信接口异常:'.$e->getMessage());
        }
        if (!$result['code']) {
            return ApiReturn::r(0, [], '发送失败，错误代码：' . $result['code'] . ' 错误信息：' . $result['msg']);
        } else {
            if($data['is_test'] == 1){
                return ApiReturn::r(1, ['code'=>$phoneVerify], '发送成功');
            }
            return ApiReturn::r(1, [], '发送成功');
        }
    }

}
