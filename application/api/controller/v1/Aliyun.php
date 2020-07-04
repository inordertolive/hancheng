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
use service\ApiReturn;
use app\admin\model\Upload;

/**
 * 阿里云系列插件接口控制器
 * @author 似水星辰 [2630481389@qq.com]
 * @package app\api\aliyun
 */
class Aliyun extends Base
{

    /**
     * 阿里云OSS直传服务端签名
     * @return \think\response\Json
     * @author 似水星辰 <2630481389@qq.com>
     */
    public function get_oss_sign()
    {
        $data = request()->post();

        $token = session('osstoken');

        $checkToken = $data["token"] ?? '';
        if($checkToken){
            if(!$checkToken || $checkToken !== $token){
                return ApiReturn::r(0,'','上传令牌失效');
            }
        }else{
            $ret =  Api::checkSign($data);
            if($ret){
                return $ret;
            }
        }
        $md5 = $data["filemd5"] ?? '';
        if(!$md5){
            return ApiReturn::r(0,'','filemd5参数必传');
        }
        $fileSize = $data["filesize"] ?? 0;
        if(!$fileSize){
            return ApiReturn::r(0,'','filesize参数必传');
        }
        $fileName = $data["filename"] ?? '';
        if(!$fileName){
            return ApiReturn::r(0,'','filename参数必传');
        }

        $config = addons_config('AliyunOss');

        $error_msg = '';
        if ($config['ak'] == '') {
            $error_msg = '未填写阿里云OSS【AccessKeyId】';
        } elseif ($config['sk'] == '') {
            $error_msg = '未填写阿里云OSS【AccessKeySecret】';
        } elseif ($config['bucket'] == '') {
            $error_msg = '未填写阿里云OSS【Bucket】';
        } elseif ($config['endpoint'] == '') {
            $error_msg = '未填写阿里云OSS【Endpoint】';
        }

        if ($error_msg != '') {
            return $this->errMsg($error_msg);
        }

        // 访问域名
        if ($config['cname'] == 0) {
            $domain   = 'http://'.$config['bucket'].'.'.$config['endpoint'].'/';
        } else {
            $domain   = 'http://'.$config['domain'].'/';
        }

        $id = $config['ak'];          // 请填写您的AccessKeyId。
        $key = $config['sk'];     // 请填写您的AccessKeySecret。
        // $host的格式为 bucketname.endpoint，请替换为您的真实信息。
        $host = $domain;

        $dir = 'files/'.date('Y-m-d').'/';          // 用户上传文件时指定的前缀。

        $now = time();
        $expire = 30;  //设置该policy超时时间是10s. 即这个policy过了这个有效时间，将不能访问。
        $end = $now + $expire;
        $expiration = $this->gmt_iso8601($end);


        //最大文件大小.用户可以自己设置
        $condition = array(0 => 'content-length-range', 1 => 0, 2 => 1048576000);
        $conditions[] = $condition;

        // 表示用户上传的数据，必须是以$dir开始，不然上传会失败，这一步不是必须项，只是为了安全起见，防止用户通过policy上传到别人的目录。
        $start = array(0 => 'starts-with', 1 => '$key', 2 => $dir);
        $conditions[] = $start;


        $arr = array('expiration' => $expiration, 'conditions' => $conditions);
        $policy = json_encode($arr);
        $base64_policy = base64_encode($policy);
        $string_to_sign = $base64_policy;
        $signature = base64_encode(hash_hmac('sha1', $string_to_sign, $key, true));
        $ext = pathinfo($fileName, PATHINFO_EXTENSION);

        $response = array();
        $response['key'] = $dir . $md5 . '.' . $ext ;
        $response['policy'] = $base64_policy;
        $response['OSSAccessKeyId'] = $id;
        $response['success_action_status'] = 200;
        $response['signature'] = $signature;
        $response['host'] = $host;
        $data = \json_encode( [
            'name'=>$fileName,
            'md5'=>$md5,
            'path'=>$domain .$response['key'],
            'ext'=>$ext,
            'size'=>$fileSize,
        ]);

        $redis = \app\common\model\Redis::handler();
        $redis->delete("aliyun_ossapi_".$md5);
        $redis->set("aliyun_ossapi_".$md5,$data,86400);

        return ApiReturn::r(200,[
            'host'=>$host,
            'aliyunData'=>$response,
            'saveToken'=> $md5,
        ]);
    }

    /**
     * @param $time
     * @return string
     * @author 似水星辰 [ 2630481389@qq.com ]
     */
    public function gmt_iso8601($time)
    {
        $dtStr = date("c", $time);
        $mydatetime = new \DateTime($dtStr);
        $expiration = $mydatetime->format(\DateTime::ISO8601);
        $pos = strpos($expiration, '+');
        $expiration = substr($expiration, 0, $pos);
        return $expiration . "Z";
    }

    /**
     * 保存外部文件到附件表
     * @param array $record 行为日志
     * @author 似水星辰 [2630481389@qq.com]
     * @return mixed
     */
    public function save_file() {
        $token = input("saveToken");
        if(!$token){
            return ApiReturn::r(0,'','缺少token');
        }
        $redis = \app\common\model\Redis::handler();

        $data = $redis->get("aliyun_ossapi_" . $token);
        if(!$data){
            return ApiReturn::r(0,'','token已失效');
        }
        $data = \json_decode($data,true);
        $redis->delete("aliyun_ossapi_" . $token);
        $uid = $data['uid'] ?? 0;
        $file_info = [
            'uid' =>  $uid,
            'name' => $data['name'],
            'md5' =>  $data['md5'],
            'path' => $data['path'],
            'ext' =>  $data['ext'],
            'size' => $data['size'],
            'module' => 'api',
            'driver' => 'aliyunoss',
        ];
        $file = Upload::where("md5", $data['md5'])->find();
        if ($file) {
            // 返回成功信息
            return ApiReturn::r(1,[
                'id'   => $file['id'],
                'name' => $file['name'],
                'path'  => $file['path'],
            ],'已存在该MD5');
        }
        $file = Upload::create($file_info);
        if ($file) {
            // 返回成功信息
            return ApiReturn::r(1,[
                'id'   => $file['id'],
                'name' => $file['name'],
                'path'  => $file['path'],
            ]);
        }
        return ApiReturn::r(0,'','上传失败');
    }

}
