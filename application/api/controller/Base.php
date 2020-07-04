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

namespace app\api\controller;

use app\common\model\Api;
use think\Controller;
use service\ApiReturn;

// API基础控制器

class Base extends Controller
{
    // 返回字段名称 数组
    public $fname;

    public function __construct()
    {
        parent::__construct();
		header('Access-Control-Allow-Origin:*');
        $hash = input('hash');
        $data = \app\common\model\ApiFields::getCacheFields($hash, 1);
        $this->fname = [];
        foreach ($data as $v) {
            $this->fname[$v['fieldName']] = [
                'isMust' => $v['isMust'],
                'default' => $v['default']
            ];
        }
    }

    /**
     * API入口，修改验证规则机制
     * @param string $hash
     * @author 似水星辰 [ 2630481389@qq.com ]
     * @return action
     */
    public function iniApi($version,$hash)
    {
        $ret = Api::init($hash);
        if ($ret) {
            return $ret;
        }
        ApiReturn::$user = Api::$user;
        return action(Api::$apiInfo['apiName'], [Api::$param, Api::$user], 'controller\\'.$version);
    }

    /**
     * 数据过滤转换
     * 对FNAME进行了重写，所有字段若未定义按照默认值填入
     * @author 似水星辰 [ 2630481389@qq.com ]
     * @param $data
     * @param null $whiteList
     * @return array
     */
    public function filter($data, $whiteList = null)
    {
        $whiteList = $whiteList === null ? $this->fname : $whiteList;
        $newData = array();
        foreach ($whiteList as $key => $val) {
            if (is_array($val)) {
                //若不是非必填且该字段未定义，则忽略
                if (!$val['isMust'] && !isset($data[$key])) {
                    continue;
                }
                $newData[$key] = $data[$key] ?? $val['default'];//检查字段，若不存在则写入默认值
            } else {
                $newData[$val] = $data[$val] ?? '';
            }
        }
        return $newData;
    }

}
