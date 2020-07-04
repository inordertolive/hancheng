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

use think\Model;

/**
 * 附件模型
 * @package app\admin\model
 */
class Upload extends Model
{
    // 设置当前模型对应的完整数据表名称
    protected $table = '__UPLOAD__';

    // 自动写入时间戳
    protected $autoWriteTimestamp = true;
	
	/**
     * 在地址前加上域名
     * @param int $path 附件保存在数据库的路径，本地上传的一般是 uploads开头的地址，OSS上传的是http/https开头的地址
     * @param bool $is_default 无图片时是否用默认图片代替,可以传默认图片的名称  放到 public/images目录下 格式为png
     * @author 似水星辰 [ 2630481389@qq.com ]
     * @return string
     */
    public function getFileUrl($path, $is_default = true) {
        //如果图片不存在，判断是否用默认图片
        $domain = \think\Facade\Config::get('web_site_domain');
        $domain = rtrim($domain, '/');
        if (!$path) {
            if(!$is_default){
                return '';
            }          
            $is_default = true=== $is_default ?  'none' : $is_default;       
            return $domain . '/images/'.$is_default.'.png';
        }
        //分析图片的URL地址，如果存在scheme协议头则是OSS上传的
        $parse_url = parse_url($path);
        if (!empty($parse_url['scheme'])) {
            return $path;
        }
        //若地址是uploads开头，则添加 PUBLIC_PATH常量
        if (0 === strpos($path, "uploads")) {
            $path = PUBLIC_PATH . $path;
        }
        //如果开头有斜线删除掉
        $path = ltrim($path, "/");      
        return $domain. '/' . $path;
    }

    /**
     * 根据附件id获取路径
     * @param  string|array $id 附件id
     * @param  int $type 类型：0-补全目录，1-直接返回数据库记录的地址
     * @return string|array     路径
	 * @author 似水星辰 [ 2630481389@qq.com ]
     */
    public function getFilePath($id = '', $type = 0)
    {
        if (is_array($id)) {
            $data_list = $this->where('id', 'in', $id)->select();
            $paths = [];
            foreach ($data_list as $key => $value) {
                $paths[$key] = $value['path'];
            }
            return $paths;
        } else {
            $data = $this->where('id', $id)->find();
            if ($data) {
                return $data['path'];
            } /*else {
                return config('web_site_domain')  .'/static/admin/images/none.png';
            }*/
        }
    }

    /**
     * 根据图片id获取缩略图路径，如果缩略图不存在，则返回原图路径
     * @param string $id 图片id
     * @author 似水星辰 [ 2630481389@qq.com ]
     * @return mixed
     */
    public function getThumbPath($id = '')
    {
        $result = $this->where('id', $id)->field('path,driver,thumb')->find();
        if ($result) {
            return $result['thumb'] != '' ? $result['thumb'] : $result['path'];
        } else {
            return config('web_site_domain')  .'/static/admin/images/none.png';
        }
    }

    /**
     * 根据附件id获取名称
     * @param  string $id 附件id
     * @return string     名称
     */
    public function getFileName($id = '')
    {
        return $this->where('id', $id)->value('name');
    }
}
