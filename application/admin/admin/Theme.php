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

namespace app\admin\admin;

use app\admin\model\Config;
/**
 * 主题控制器
 * @author 似水星辰 [ 2630481389@qq.com ]
 */
class Theme extends Base
{
    /**
     * 主题列表
     * @author 似水星辰 [ 2630481389@qq.com ]
     * @return mixed
     */
    public function index()
    {
        cookie('__forward__', $_SERVER['REQUEST_URI']);
		//排除的系统目录
        $exclude = config('notread_module');
        $theme =  ROOT_PATH . 'public/theme';
        $filed = glob($theme . '/*' );
        $count = 0;

        foreach ($filed as $key => $v) {
            if (is_dir($v) == false) {
                continue;
            }
            $arr[$key]['name'] = basename($v);
            if (is_file($theme . "/" . $arr[$key]['name'] . '/preview.jpg')) {
                $arr[$key]['preview'] = '/'.str_replace(ROOT_PATH.'public/', "", $theme) . "/" . $arr[$key]['name'] . '/preview.jpg';
            } else {
                $arr[$key]['preview'] = '/static/admin/images/none.png';
            }
            if (config('web_default_theme') == $arr[$key]['name']) {
                $arr[$key]['use'] = 1;
            }
            if (in_array($arr[$key]['name'], $exclude)) {
                unset($arr[$key]);//排除系统自带的目录
            }
            $count++;
        }
		$this->assign('list',$arr);
		return $this->fetch();
    }

    /**
     * 选择主题
     * @param null $name 主题目录名
     * @throws \think\Exception
     * @throws \think\exception\PDOException
     * @author 似水星辰 [ 2630481389@qq.com ]
     */
    public function choose($name = null){
        if (empty($name)) {
            $this->error('主题名称不能为空！');
        }
        if ($name == config('web_default_theme')) {
            $this->error('主题未改变！');
        }

        $res = Config::where('name','web_default_theme')->update(['value'=>$name]);
        if($res){
            $this->ClearCache();
            // 记录行为
            action_log('admin_choose_theme', 'admin', 0, UID, $name);
            $this->success('切换成功！');
        }else{
            $this->error('切换失败！');
        }
    }
}
