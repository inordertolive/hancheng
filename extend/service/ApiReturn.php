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

namespace service;
/**
 * Api请求统一返回
 * @author 似水星辰 [ 2630481389@qq.com ]
 */
class ApiReturn
{
    static public $user = [];

    static public $userFields = [
        'id' => '会员ID',
        'user_nickname' => '会员昵称',
        'head_img' => '会员头像',
        'sex' => '会员性别',
        'user_type' => '会员类型',
        'user_level' => '会员等级',
        'status' => '状态'
    ];

    static public $Code = [
        '1' => ['1', '操作成功'],
        '0' => ['0', '操作失败'],
        '-1' => ['-1', 'hash参数无效'],

        '-101' => ['-101', '应用AppID非法'],
        '-102' => ['-102', '缺少SignatureNonce参数'],
        '-103' => ['-103', '缺少Signature参数'],
        '-104' => ['-104', '缺少Timestamp参数'],
        '-105' => ['-105', '请求时间不正确'],
        '-106' => ['-106', '请求时间已过期'],
        '-107' => ['-107', 'APPID不正确'],
        '-108' => ['-108', '签名不正确'],

        '-201' => ['-201', '缺少UserToken令牌'],
        '-202' => ['-202', 'UserToken令牌无效'],
        '-203' => ['-203', 'UserToken令牌过期'],
        '-230' => ['-230', '应用已禁用'],

        '-300' => ['-300', 'User异地登录'],
        '-800' => ['-800', '没有数据'],
        '-900' => ['-900', '参数错误'],
        '-999' => ['-999', '系统错误'],
    ];

    static public function r($code = 1, $data = null, $info = '')
    {
        $user = '';
        if (ApiReturn::$user) {
            $user = [];
            foreach (ApiReturn::$userFields as $k => $v) {
                $user[$k] = ApiReturn::$user[$k] ?? '';
            }
        }
        if (array_key_exists($code, ApiReturn::$Code)) {
            if (empty($info)) {
                $info = ApiReturn::$Code[$code][1];
            } else {
                $info = $info;
            }

            $info_arr = [
                'code' => ApiReturn::$Code[$code][0],
                'msg' => $info,
                'data' => $data,
                'time' => time(),
                'user' => $user,
            ];
        } else {
            if (empty($info)) {
                $info = ApiReturn::$Code[1][1];
            } else {
                $info = $info;
            }
            $info_arr = [
                'code' => $code,
                'msg' => $info,
                'data' => $data,
                'time' => time(),
                'user' => $user,
            ];
        }
        ob_end_clean();
        return json($info_arr);
    }
}