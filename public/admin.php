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

// [ 后台入口文件 ]
namespace think;
// 定义应用目录
define('TEMPLETE_PATH', __DIR__ . '/../public/theme/');
define('LOG_PATH',	__DIR__ . '/../runtime/log/');
define('CACHE_PATH',__DIR__ . '/../runtime/cache/');
define('TEMP_PATH',	__DIR__ . '/../runtime/temp/');
define('ROOT_PATH',	__DIR__ . '/../');
define('APP_PATH', ROOT_PATH.'application/');

// 定义为后台入口
define('ENTRANCE', 'admin');

// 加载基础文件
require __DIR__ . '/../thinkphp/base.php';

// 检查是否安装
if(!is_file('./../data/install.lock')) {
    header('location: /');
} else {
	// 执行应用并响应
	Container::get('app')->run()->send();
}
