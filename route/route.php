<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006~2018 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------

Route::get('think', function () {
    return 'hello,ThinkPHP5!';
});

Route::get('hello/:name', 'index/hello');
Route::rule('api/:version/:hash','api/base/iniApi','GET|POST');
// API文档列表
Route::rule('apilist','api/apihelp.index/index','GET');
// API文档详情
Route::rule('apiinfo/:hash','api/apihelp.index/apiinfo','GET');
// API错误码详情
Route::rule('errorlist','api/apihelp.index/errorlist','GET');
return [

];
