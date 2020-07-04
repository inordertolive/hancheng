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
 * 应用appId和appSecret表
 * @package app\common\model
 */
class Apiapp extends Model {

    protected $table = '__ADMIN_API_APP__';

    // 开启自动写入时间戳字段
    protected $autoWriteTimestamp = true;

    //只读字段,一旦写入，就无法更改。
    protected $readonly = ['app_id', 'app_secret'];

    //关联模型
    public function ApiApptoken() {
        return $this->hasOne('ApiApptoken', 'app_id', 'app_id');
    }
 
    /**
     * 查询APP配置参数并缓存
     * @author 似水星辰 [ 2630481389@qq.com ]
     * @param string $appid
     * @return object|null
     */
    public static function getCache($appid){
        $key = 'apiApp_'.$appid;
        $app= cache($key);
        if(!$app){
            $app =  self::get(['app_id'=> $appid]);          
            cache($key,$app,7200);
        }
        return $app;        
    }

}
