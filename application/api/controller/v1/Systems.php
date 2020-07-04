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
// 系统公共接口控制器

namespace app\api\controller\v1;

use app\api\controller\Base;
use service\ApiReturn;


class Systems extends Base {

    /**
     * 获取系统最新版本
     * @return void
     * @author 似水星辰 [ 2630481389@qq.com ]
     * @created 2019/8/29 19:09
     */
    public function get_version($data = []){
        $info['version'] = config('version');
        $info['version_name'] = config('version_name');
        $info['is_update_apk'] = config('is_update_apk');
        $info['app_readme'] = config('app_readme');
        $info['app_size'] = config('app_size');
        $info['app_name'] = config('app_name');
        $info['app_ios_download'] = config('app_ios_download');
        $info['app_type'] = config('app_type');
        $info['force'] = config('force');
        return ApiReturn::r(1, $info, '请求成功');
    }
}
