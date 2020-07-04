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

namespace app\admin\model;

use think\Model as ThinkModel;;

/**
 * 主题模型
 * @package app\admin\model
 */
class Theme extends ThinkModel
{
    // 自动写入时间戳
    protected $autoWriteTimestamp = true;
	
	/*
	 * 获取并过滤模板文件列表
	 * @param $data array 模板文件列表
	 * @param $suffix string 模板后缀
	 * @param $mark string模板标识
	 * @author 似水星辰 [ 2630481389@qq.com ]
     * @return array
     */
	public function get_html_templets($data, $mark = 'list', $suffix = '.html'){
		foreach($data as $k=>$t){
			if(strpos($t,$suffix) === false || strpos($t,$mark) === false){
				unset($data[$k]);
			}
		}
		return $data;
	}
}