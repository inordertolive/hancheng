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
use app\user\model\User as UserModel;
use app\common\model\Api;
use app\common\model\LogSms;
use think\helper\Hash;
use think\Db;
use service\ApiReturn;
use service\Str;

/**
 * 用户接口
 * @package app\api\controller\v1
 */
class User extends Base
{

    /**
     * 获取用户登录信息
     * @param string $data 传入的数据，包含username和password
     * @return \think\response\Json
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function user_name_login($data = '')
    {
        //手机号登录
        $map['mobile'] = $data['mobile'];
        $user = UserModel::where($map)->field('id,user_name,password,user_nickname,status,head_img,sex,user_type,user_level,mobile,birthday,client_id')->find();
        if (!$user) {
            return ApiReturn::r(-999, [], '此用户不存在，请先注册！');
        }

        if ($user) {
            if (!Hash::check((string)$data['password'], $user['password'])) {
                return ApiReturn::r(0, [], '账号或者密码错误！');
            }
            if (!$user['status']) {
                return ApiReturn::r(-999, [], '此用户被禁用，请联系客服咨询');
            }

            unset($user['password']);
            //获取用户附加信息
            $user_info = Db::name('user_info')->where('user_id', $user['id'])->find();
            //获取登录需要返回的信息
            $jsonList = $this->get_login_info($user, $user_info);

            return ApiReturn::r(1, ['userinfo' => $jsonList], '登录成功'); //返回给客户端token信息
        } else {
            return ApiReturn::r(0, [], '该用户不存在');
        }
    }

    /**
     * 用户使用手机验证码登录
     * @param string $data
     * @return \think\response\Json
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @author 似水星辰 [ 2630481389@qq.com ]
     * @since 2019/4/7 16:31
     */
    public function user_mobile_login($data = '')
    {
        $map['mobile'] = $data['mobile'];
        $user = UserModel::where($map)->field('id,user_name,user_nickname,status,head_img,sex,user_type,user_level,mobile,birthday,client_id')->find();
        if (!$user) {
            return ApiReturn::r(-999, [], '没有此用户，请注册后登录');
        }

        if ($user) {
            $logSmsModel = new LogSms();
            Db::startTrans();
            try {
                $result = $logSmsModel->verify_code($data['code'], $data['mobile'], $data['type']);
                if (!$result) {
                    return ApiReturn::r(-999, [], '登录失败,验证码无效或已过期！');
                }
                if (!$user['status']) {
                    return ApiReturn::r(-999, [], '此用户被禁用，请联系客服咨询');
                }
                //获取用户附加信息
                $user_info = Db::name('user_info')->where('user_id', $user['id'])->find();
                //获取登录需要返回的信息
                $jsonList = $this->get_login_info($user, $user_info);

                // 提交事务
                Db::commit();
            } catch (\Exception $e) {
                // 回滚事务
                Db::rollback();
                return ApiReturn::r(0, [], $e->getMessage());
            }
            return ApiReturn::r(1, ['userinfo' => $jsonList], '登录成功'); //返回给客户端token信息
        } else {
            return ApiReturn::r(0, [], '该用户不存在');
        }
    }

    /**
     * 社会化第三方登录
     * @author 似水星辰 [ 2630481389@qq.com ]
     * @created 2019/9/9 22:28
     */
    public function user_social_login($data = [], $user = [])
    {
        if ($data['type'] == 1) {
            if (!$data['wx_unionid']) {
                return ApiReturn::r(0, [], '参数错误，登录失败');
            }
            // 获取会员附加表信息
            $user_info = Db::name('user_info')->where('wx_unionid', $data['wx_unionid'])->find();
        }

        if ($data['type'] == 2) {
            if (!$data['qq_unionid']) {
                return ApiReturn::r(0, [], '参数错误，登录失败');
            }
            // 获取会员附加表信息
            $user_info = Db::name('user_info')->where('qq_unionid', $data['qq_unionid'])->find();
        }

        if (!$user_info) {
            return ApiReturn::r(-999, [], '没有此用户，开始注册绑定跳转');
        }
        $user = UserModel::where('id', $user_info['user_id'])->field('id,user_name,user_nickname,status,head_img,sex,user_type,user_level,mobile,birthday,client_id')->find();
        if ($user) {
            Db::startTrans();
            try {
                if (!$user['status']) {
                    return ApiReturn::r(-999, [], '此用户被禁用，请联系客服咨询');
                }
                // 获取登录需要返回的信息
                $jsonList = $this->get_login_info($user, $user_info);
                // 提交事务
                Db::commit();
            } catch (\Exception $e) {
                // 回滚事务
                Db::rollback();
                return ApiReturn::r(0, [], $e->getMessage());
            }
            return ApiReturn::r(1, ['userinfo' => $jsonList], '登录成功'); //返回给客户端token信息
        } else {
            return ApiReturn::r(0, [], '该用户不存在');
        }
    }

    /**
     * 第三方绑定账号，无账号则自动注册绑定
     * @author 似水星辰 [ 2630481389@qq.com ]
     * @created 2019/9/10 16:08
     */
    public function bind_wechat_account($data)
    {
        $logSmsModel = new LogSms();
        Db::startTrans();
        try {
            $result = $logSmsModel->verify_code($data['code'], $data['mobile'], $data['type']);
            if (!$result) {
                return ApiReturn::r(-999, [], '登录失败,验证码无效或已过期！');
            }
            $map['mobile'] = $data['mobile'];
            $user = UserModel::where($map)->field('id,user_name,user_nickname,status,head_img,sex,user_type,user_level,mobile,birthday,client_id,password')->find();
            if ($user) {
                $is_pass = Hash::check($data['password'],$user['password']);//原密码
                if (!$is_pass) {
                    exception('手机号已存在，验证密码错误，绑定失败');
                }
                if($user['head_img']=="" || $user['head_img']==0){
                    //如果头像为空，则更新头像,更新性别
                    UserModel::where('id', $user['id'])->update(['head_img'=>$data['avatarUrl'],'sex' => $data['gender']]);
                    $user['head_img'] = $data['avatarUrl'];
                    $user['sex'] = $data['gender'];
                }
                //添加wx_unionid
                $res = Db::name('user_info')->where('user_id', $user['id'])->update(['wx_unionid' => $data['unionId'], 'wx_openid' => $data['openId']]);
                if (!$res) {
                    exception('绑定失败');
                }
            } else {
                $info['user_nickname'] = $data['nickName'];
                $info['sex'] = $data['gender'];
                $info['head_img'] = $data['avatarUrl'];
                $info['mobile'] = $data['mobile'];
                $info['client_id'] = $data['client_id'];
                $info['password'] = $data['password'];
                $id = $this->get_reg_data($info);
                if ($id) {
                    //添加wx_unionid
                    $res1 = Db::name('user_info')->where('user_id', $id)->update(['wx_unionid' => $data['unionId'], 'wx_openid' => $data['openId']]);

                    //添加微信提现账号
                    $account = [
                        'user_id' => $id,
                        'account_id' => $data['openId'],
                        'true_name' => $data['nickName'],
                        'account_type' => 1,
                        'is_default' => 1,
                        'create_time' => time(),
                    ];
                    $res2 = Db::name('user_withdraw_account')->insert($account);
                    if (!$res1 || !$res2) {
                        exception('绑定失败');
                    }
                } else {
                    exception('注册失败');
                }
                $user = UserModel::where('id', $id)->field('id,user_name,user_nickname,status,head_img,sex,user_type,user_level,mobile,birthday,client_id')->find();
            }
            //获取会员附加信息
            $user_info = Db::name('user_info')->where('user_id', $id)->find();
            //获取登录需要返回的信息
            $jsonList = $this->get_login_info($user, $user_info);

            // 提交事务
            Db::commit();
        } catch (\Exception $e) {
            // 回滚事务
            Db::rollback();
            return ApiReturn::r(0, [], $e->getMessage());
        }
        return ApiReturn::r(1, ['userinfo' => $jsonList], '绑定成功'); //返回给客户端token信息
    }

    /**
     * 生成登录需要的信息
     * @author 似水星辰 [ 2630481389@qq.com ]
     * @created 2019/9/10 14:58
     */
    public function get_login_info($user, $user_info)
    {
        // 请求接口的token
        $exp_time1 = 2592000; //token过期时间,这里设置2个小时
        $scopes1 = 'role_access'; //token标识，请求接口的token
        $access_token = Api::createToken($user, $exp_time1, $scopes1);
        $uuid = Str::uuid();
        cache('userinfo_' . $user['id'], null);
        cache('user_token_' . $uuid, $access_token);


        $jsonList = [
            'user_token' => $uuid,
            'id' => $user['id'],
            'head_img' => get_file_url($user['head_img']),
            'user_name' => $user['user_name'],
            'user_nickname' => $user['user_nickname'],
            'sex' => $user['sex'],
            'user_type' => $user['user_type'],
            'user_level' => $user['user_level'],
            'mobile' => preg_replace('/(\d{3})\d{4}(\d{4})/', '$1****$2', $user['mobile']),
            'birthday' => date('Y-m-d', $user['birthday']),
            'address' => $user_info['address'],
            'address_code' => explode(',', $user_info['address_code']),
            'client_id' => $user['client_id']
        ];

        return $jsonList;
    }

    /**
     * 获取会员详细信息
     * @param string $data
     * @param string $user
     * @return \think\response\Json
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @author 似水星辰 [ 2630481389@qq.com ]
     */
    public function get_user_info($data = [], $user = [])
    {
        if ($data['user_id']) {
            $info = UserModel::alias('u')->join('user_info i', 'u.id=i.user_id', 'left')
                ->where('u.id', $data['user_id'])->cache('userinfo_' . $data['user_id'], '3600')
                ->field('i.*,u.user_level')->field('password,wechat_id,user_name', true)->find();
        } else {
            $info = cache('userinfo_' . $user['id']);
        }

        if ($info) {
            $info['mobile'] = preg_replace('/(\d{3})\d{4}(\d{4})/', '$1****$2', $info['mobile']);
            $info['head_img'] = get_file_url($info['head_img']);
            //$info['sex'] = get_sex($info['sex']);
            $info['age'] = get_age($info['birthday']);
            $jsonList = $this->filter($info, $this->fname);
            return ApiReturn::r(1, $jsonList, '请求成功');
        }
        return ApiReturn::r(0, [], '请登录');
    }

    /**
     * 会员注册
     * @param array $data
     * @param array $user
     * @return void
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @throws \think\exception\PDOException
     * @author 似水星辰 [ 2630481389@qq.com ]
     * @since 2019/4/10 15:45
     */
    public function registerUser($data = [], $user = [])
    {
        // 启动事务
        Db::startTrans();
        try {
            $logSmsModel = new LogSms();
            $result = $logSmsModel->verify_code($data['code'], $data['mobile'], $data['type']);
            if (!$result) {
                exception('验证码错误');
            }

            $id = $this->get_reg_data($data);
            if (!$id) {
                exception('注册失败');
            }
            // 提交事务
            Db::commit();
        } catch (\Exception $e) {
            // 回滚事务
            Db::rollback();
            $msg = $e->getMessage();
            return ApiReturn::r(0, [], $msg);
        }

        $user = UserModel::where('id', $id)->field('id,user_name,user_nickname,status,head_img,sex,user_type,user_level,mobile,birthday,client_id')->find();
        //获取用户附加信息
        $user_info = Db::name('user_info')->where('user_id', $id)->find();
        // 获取登录需要返回的信息
        $jsonList = $this->get_login_info($user, $user_info);
        return ApiReturn::r(1, ['userinfo' => $jsonList], '注册成功');
    }

    /**
     * 注册用户信息
     * @param $data
     * @author 似水星辰 [ 2630481389@qq.com ]
     * @created 2019/9/10 17:03
     */
    public function get_reg_data($data)
    {
        // 启动事务
        Db::startTrans();
        try {
            $res=$this->check_password($data['password']);
            if(!$res){
                exception('请设置密码为6-32位字母加数字的组合');
            }
            if(isset($data['invite_code']) && $data['invite_code'] != ""){
                $lastid = Db::name('user_info')->where('invite_code', $data['invite_code'])->value('user_id');
                if($lastid){
                    $user_data['lastid'] = $lastid;
                }
            }
            $user_data['password'] = $data['password'];
            $user_data['mobile'] = $data['mobile'];
            $user_data['user_name'] = $data['user_nickname'] ? $data['user_nickname'] : '用户' . rand(10000, 99999);
            $user_data['client_id'] = $data['client_id'];
            $user_data['user_type'] = 0;
            $user_data['head_img'] = $data['head_img'] ? $data['head_img'] : 0;
            $user_data['user_nickname'] = $data['user_nickname'] ? $data['user_nickname'] : $user_data['user_name'];
            $user_data['create_time'] = time();
            $user_data['status'] = 1;
            $user_data['sex'] = $data['sex'] ? $data['sex'] : 0;
            $user_data['birthday'] = time();

            //注册账号
            $result = UserModel::create($user_data);
            $id = $result->id;
            if (!$id) {
                exception('注册会员失败');
            }
            // 新增会员附加信息
            $userinfo = Db::name('user_info')->insert(['user_id' => $id, 'invite_code' => 'IC00' . $id]);
            if (!$userinfo) {
                exception('注册附加信息失败');
            }
            // 提交事务
            Db::commit();
        } catch (\Exception $e) {
            // 回滚事务
            Db::rollback();
            $msg = $e->getMessage();
            exception($msg);
        }
        return $id;
    }

    /**
     * 验证码重置密码
     * @param array $data
     * @param array $user
     * @return void
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @throws \think\exception\PDOException
     * @author 似水星辰 [ 2630481389@qq.com ]
     * @since 2019/4/10 15:45
     */
    public function forgetPassword($data = [], $user = [])
    {
        // 启动事务
        Db::startTrans();
        try {
            $map['mobile'] = $data['mobile'];
            //自定义信息，不要定义敏感信息
            $user = UserModel::where($map)->field('id,user_name,user_nickname,status,head_img,sex,user_type,user_level,mobile')->find();
            if (!$user) {
                return ApiReturn::r(-999, [], '没有此用户，请注册后登录');
            }
            $logSmsModel = new LogSms();
            $result = $logSmsModel->verify_code($data['code'], $data['mobile'], $data['type']);
            if (!$result) {
                exception('验证码错误');
            }

            $password = Hash::make($data['password']);
            $result1 = UserModel::where('mobile', $data['mobile'])->update(['password' => $password]);
            if (!$result1) {
                exception('重置密码失败');
            }
            // 提交事务
            Db::commit();
        } catch (\Exception $e) {
            // 回滚事务
            Db::rollback();
            $msg = $e->getMessage();
            return ApiReturn::r(0, [], $msg);
        }

        return ApiReturn::r(1, [], '重置密码成功');
    }

    /**
     * 旧密码验证修改密码
     * @author 朱龙飞 [ 2420541105 @qq.com ]
     * @created 2019/10/17 0003 14:13
     */
    public function forgetPassword_code($data = [], $user = [])
    {
        $userinfo_password=UserModel::where('id', $user['id'])->value('password');
        $resd = Hash::check($data['security_code'],$userinfo_password);//原密码
        if(!$resd){
            return ApiReturn::r(0, [], '原密码错误');
        }
        $res=$this->check_password($data['password']);
        if(!$res){
            return ApiReturn::r(0, [], '密码只能是6-32位字母加数字');
        }
        if($data['password'] != $data['password_code']){
            return ApiReturn::r(0, [], '新密码和确定密码不一致，请重新输入');
        }
        $password = Hash::make($data['password']);
        $result = UserModel::where('id', $user['id'])->update(['password' => $password]);
        if ($result) {
            return ApiReturn::r(1, [], '重置密码成功');
        }
        return ApiReturn::r(0, [], '重置密码失败');
    }

    /**
     * 验证密码
     * @author 朱龙飞 [ 2420541105@qq.com ]
     * @created 2019/9/16 0003 10:57
     */
    public function check_password($password)
    {
        if (preg_match("/^(?![0-9]+$)(?![a-zA-Z]+$)[0-9A-Za-z]{6,32}$/", $password)) {
            return 1;
        } else {
            return 0;
        }
    }
    /**
     * 修改会员个人资料
     * @param string $data
     * @param string $user
     * @return \think\response\Json
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @author 杨志刚 [ 1909507511@qq.com ]
     * @since 2019/4/16 15:36
     */
    public function edit_user_info($data = [], $user = [])
    {
        if (!$user) {
            return ApiReturn::r(1, [], '登录状态已失效，请重新登录');
        }
        if ($data['birthday']) {
            $result = \think\Validate::make()
                ->rule('birthday', 'date')
                ->check($data);
            if (!$result) {
                return ApiReturn::r(0, [], '生日格式有误，请重新选择');
            }
            $data['birthday'] = strtotime($data['birthday']);//时间格式转化时间戳
        }

        $data['update_time'] = time();
        if ($data['head_img'] == "") {
            unset($data['head_img']);
        }
        // 启动事务
        Db::startTrans();
        try {
            $result = UserModel::where('id', $user['id'])->update($data);
            if (!$result) {
                throw new \Exception('更新会员信息失败');
            }
            $data['updatetime'] = $data['update_time'];
            //修改会员附表
            $user_info = Db::name('user_info')->where('user_id', $user['id'])->find();
            if ($user_info) {
                $res = Db::name('user_info')->where('user_id', $user['id'])->update($data);
                if (!$res) {
                    throw new \Exception('更新会员附加信息出错');
                }
            }
            Db::commit();
        } catch (\Exception $e) {
            // 更新失败 回滚事务
            Db::rollback();
            return ApiReturn::r(0, [], $e->getMessage());
        }
        cache('userinfo_' . $user['id'], null);
        $user_data = UserModel::where('id', $user['id'])->find();
        $jsonList = $this->get_login_info($user_data, $user_info);
        return ApiReturn::r(1, ['userinfo' => $jsonList], '操作成功');
    }

    /**
     * 新增或更换绑定的手机号
     * @return \think\response\Json
     * @author 似水星辰 [ 2630481389@qq.com ]
     * @created 2019/8/23 10:16
     */
    public function bind_mobile($data = [], $user = [])
    {
        //注意，step=1时，mobile是旧手机号，step=2时，mobile是新手机号
        // 启动事务
        Db::startTrans();
        try {

            $logSmsModel = new LogSms();
            if ($data['step'] == 1) {
                $data['mobile'] = Db::name('user')->where('id',$user['id'])->value('mobile');
                if (!preg_match("/^1\d{10}$/", $data['mobile'])) {
                    throw new \Exception('手机号码格式错误');
                }
            }
            $result = $logSmsModel->verify_code($data['code'], $data['mobile'], $data['type']);
            if (!$result) {
                exception('验证码错误');
            }

            if ($data['step'] == 1) {
                Db::commit();
                return ApiReturn::r(1, [], '验证成功');
            }

            $res = UserModel::where(['mobile' => $data['mobile']])->count();
            if ($res) {
                throw new \Exception('此手机号已存在，请更换手机号');
            }

            $res1 = UserModel::where('id', $user['id'])->update(['mobile' => $data['mobile']]);
            if (!$res1) {
                throw new \Exception('绑定失败');
            }
            Db::commit();
        } catch (\Exception $e) {
            // 更新失败 回滚事务
            Db::rollback();
            return ApiReturn::r(0, [], $e->getMessage());
        }

        cache('userinfo_' . $user['id'], null);
        return ApiReturn::r(1, ['userinfo' => ['mobile' => $data['mobile']]], '绑定成功');
    }

    /**
     * 更新用户表中用户的设备client_id
     * @author 似水星辰 [ 2630481389@qq.com ]
     * @created 2019/9/25 14:18
     */
    public function set_client_id($data,$user){
        $result = UserModel::where('id',$user['id'])->update(['client_id'=>$data['client_id']]);
        if($result){
            return ApiReturn::r(1, [], '更新client_id成功');
        }
        return ApiReturn::r(0, [], '操作失败');
    }


}
