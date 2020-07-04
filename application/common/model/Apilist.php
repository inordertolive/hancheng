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
namespace app\common\model;

use think\Model;
/**
 * 用于保存各个API的字段规则
 * @package app\common\model
 */
class Apilist extends Model {

	protected $table = '__ADMIN_API_LIST__';

	protected $autoWriteTimestamp = true;

	//只读字段,一旦写入，就无法更改。
	protected $readonly = ['hash'];

	//关联模型
    public function api_fields() {
        return $this->hasOne('ApiFields', 'hash', 'hash');
    }

	public function getMethodTurnAttr($value, $data) {	//请求方式 method 字段 [获取器]
        $turnArr = [0=>'不限', 1=>'POST',2=>'GET'];
        return $turnArr[$data['method']];
    }

    public function getAccessTokenTurnAttr($value, $data) {	//是否需要认证AccessToken accessToken 字段 [获取器]
        $turnArr = [0=>'不验证Token', 1=>'验证Token'];
        return $turnArr[$data['accessToken']];
    }

    public function getNeedLoginTurnAttr($value, $data) {	//是否需要认证用户token needLogin 字段 [获取器]
        $turnArr = [0=>'不验证登录', 1=>'验证登录'];
        return $turnArr[$data['needLogin']];
    }

    public function getIsTestTurnAttr($value, $data) {	//是否是测试模式 isTest 字段 [获取器]
        $turnArr = [0=>'测试模式', 1=>'生产模式'];
        return $turnArr[$data['isTest']];
    }
    public function getStatusTurnAttr($value, $data) {	//接口状态 Status 字段 [获取器]
        $turnArr = [0=>'接口被禁用', 1=>'正常访问'];
        return $turnArr[$data['status']];
    }
  
    /**
     * 获取缓存APIINFO
     * @author 似水星辰 [ 2630481389@qq.com ]
     * @param string $hash 接口HASH
     * @return array|null
     */
    public static function getCacheInfo($hash){   
        static $info = null;
        $apiInfo = cache('apiInfo_' . $hash);          
        if (empty( $apiInfo)  && $info === null) {
            $apiInfo = self::get(['hash' => $hash, 'status' => 1]);
            cache('apiInfo_' . $hash, $apiInfo, 7200); //接口信息
            $info = 1;
        }       
        return   $apiInfo;
    }
}