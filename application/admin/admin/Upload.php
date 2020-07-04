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

use app\common\controller\Common;
use app\admin\model\Upload as UploadModel;
use think\Image;
use think\facade\Hook;
/**
 * 后台公共控制器
 * @package app\admin\admin
 */
class Upload extends Common
{
	/**
     * 保存文件
     * @param string $dir 附件存放的目录
     * @param string $from 来源
     * @param string $module 来自哪个模块
     * @author 似水星辰 [ 2630481389@qq.com ]
     * @return string|\think\response\Json
     */
    public function save($dir = '', $from = '', $module = '' , $returnarray = false)
    {
        // 附件大小限制
        $size_limit = $dir == 'images' ? config('upload_image_size') : config('upload_file_size');
        $size_limit = $size_limit * 1024;
        // 附件类型限制
        $ext_limit = $dir == 'images' ? config('upload_image_ext') : config('upload_file_ext');
        $ext_limit = $ext_limit != '' ? parse_attr($ext_limit) : '';
        // 缩略图参数
        $thumb = $this->request->post('thumb', '');
        // 水印参数
        $watermark = $this->request->post('watermark', '');

        // 获取附件数据
        $callback = '';

        $file_input_name = 'file';
        $file = $this->request->file($file_input_name);

        // 判断附件大小是否超过限制
        if ($size_limit > 0 && ($file->getInfo('size') > $size_limit)) {
            switch ($from) {
                case 'wangeditor':
                    return "error|附件过大";
                    break;
                default:
                    return json([
                        'code'   => 0,
                        'class'  => 'danger',
                        'info'   => '附件过大'
                    ]);
            }
        }

        // 判断附件格式是否符合
        $file_name = $file->getInfo('name');
        $file_ext  = strtolower(substr($file_name, strrpos($file_name, '.')+1));
        $error_msg = '';
        if ($ext_limit == '') {
            $error_msg = '获取文件信息失败！';
        }
        if ($file->getMime() == 'text/x-php' || $file->getMime() == 'text/html') {
            $error_msg = '禁止上传非法文件！';
        }
        if (preg_grep("/php/i", $ext_limit)) {
            $error_msg = '禁止上传非法文件！';
        }
        if (!preg_grep("/$file_ext/i", $ext_limit)) {
            $error_msg = '附件类型不正确！';
        }

        if ($error_msg != '') {
            switch ($from) {
                case 'wangeditor':
                    return "error|{$error_msg}";
                    break;
                default:
                    return json([
                        'code'   => 0,
                        'class'  => 'danger',
                        'info'   => $error_msg
                    ]);
            }
        }

        // 附件上传钩子，用于第三方文件上传扩展
        if (config('upload_driver') != 'local') {
            $hook_result = Hook::listen('upload_attachment', ['file' => $file, 'from' => $from, 'module' => $module], true);
            if (false !== $hook_result) {
                return $hook_result;
            }
        }

        // 移动到框架应用根目录/uploads/ 目录下
        $info = $file->move(ROOT_PATH.config('upload_path') . $dir);
        if($info){
            // 缩略图路径
            $thumb_path_name = '';
            // 图片宽度
            $img_width = '';
            // 图片高度
            $img_height = '';
            if ($dir == 'images') {
                $img = Image::open($info);
                $img_width  = $img->width();
                $img_height = $img->height();
                // 水印功能
                if ($watermark == '') {
                    if (config('upload_thumb_water') == 1 && config('upload_thumb_water_pic') > 0) {
                        $this->create_water($info->getRealPath(), config('upload_thumb_water_pic'));
                    }
                } else {
                    if (strtolower($watermark) != 'close') {
                        list($watermark_img, $watermark_pos, $watermark_alpha) = explode('|', $watermark);
                        $this->create_water($info->getRealPath(), $watermark_img, $watermark_pos, $watermark_alpha);
                    }
                }

                // 生成缩略图
                if ($thumb == '') {
                    if (config('upload_image_thumb') != '') {
                        $thumb_path_name = $this->create_thumb($info, $info->getPathInfo()->getfileName(), $info->getFilename());
                    }
                } else {
                    if (strtolower($thumb) != 'close') {
                        list($thumb_size, $thumb_type) = explode('|', $thumb);
                        $thumb_path_name = $this->create_thumb($info, $info->getPathInfo()->getfileName(), $info->getFilename(), $thumb_size, $thumb_type);
                    }
                }
            }

            // 获取附件信息
            $file_info = [
                'uid'    => session('admin_auth.uid') ? session('admin_auth.uid') : 0,
                'name'   => $file->getInfo('name'),
                'mime'   => $file->getInfo('type'),
                'path'   => config('web_site_domain').'/uploads/' . $dir . '/' . str_replace('\\', '/', $info->getSaveName()),
                'ext'    => $info->getExtension(),
                'size'   => $info->getSize(),
                'md5'    => $info->hash('md5'),
                'sha1'   => $info->hash('sha1'),
                'thumb'  => $thumb_path_name,
                'module' => $module,
                'width'  => $img_width,
                'height' => $img_height,
            ];

            // 写入数据库
            if ($file_add = UploadModel::create($file_info)) {
                $file_path = $file_info['path'];
                switch ($from) {
                    case 'wangeditor':
                        return $file_path;
                        break;
                    default:
                        $str = [
                            'code'   => 1,
                            'info'   => '上传成功',
                            'class'  => 'success',
                            'id'     => $file_add['id'],
                            'path'   => $file_path
                        ];
                        if ($returnarray){
                            return $str;
                        }

                        return json($str);
                }
            } else {
                switch ($from) {
                    case 'wangeditor':
                        return "error|上传失败";
                        break;
                    default:
                        $str = ['code' => 0, 'class' => 'danger', 'info' => '上传失败'];
                        if ($returnarray){
                            return $str;
                        }
                        return json($str);
                }
            }
        }else{
            switch ($from) {
                case 'wangeditor':
                    return "error|".$file->getError();
                    break;
                default:
                    $str = ['code' => 0, 'class' => 'danger', 'info' => $file->getError()];
                    if ($returnarray){
                        return $str;
                    }
                    return json($str);
            }
        }
    }
    /**
     * 创建缩略图
     * @param string $file 目标文件，可以是文件对象或文件路径
     * @param string $dir 保存目录，即目标文件所在的目录名
     * @param string $save_name 缩略图名
     * @param string $thumb_size 尺寸
     * @param string $thumb_type 裁剪类型
     * @return string 缩略图路径
     */
    private function create_thumb($file = '', $dir = '', $save_name = '', $thumb_size = '', $thumb_type = '')
    {
        // 获取要生成的缩略图最大宽度和高度
        $thumb_size = $thumb_size == '' ? config('upload_image_thumb') : $thumb_size;
        list($thumb_max_width, $thumb_max_height) = explode(',', $thumb_size);
        // 读取图片
        $image = Image::open($file);
        // 生成缩略图
        $thumb_type = $thumb_type == '' ? config('upload_image_thumb_type') : $thumb_type;
        $image->thumb($thumb_max_width, $thumb_max_height, $thumb_type);
        // 保存缩略图
        $thumb_path = config('upload_path') . DIRECTORY_SEPARATOR . 'images/' . $dir . '/thumb/';
        if (!is_dir($thumb_path)) {
            mkdir($thumb_path, 0766, true);
        }
        $thumb_path_name = $thumb_path. $save_name;
        $image->save($thumb_path_name);
        $thumb_path_name = 'uploads/images/' . $dir . '/thumb/' . $save_name;
        return $thumb_path_name;
    }

    /**
     * 添加水印
     * @param string $file 要添加水印的文件路径
     * @param string $watermark_img 水印图片id
     * @param string $watermark_pos 水印位置
     * @param string $watermark_alpha 水印透明度
     */
    private function create_water($file = '', $watermark_img = '', $watermark_pos = '', $watermark_alpha = '')
    {
        $path = model('admin/attachment')->getFilePath($watermark_img, 1);
        $thumb_water_pic = realpath(Env::get('root_path') . 'public/' . $path);
        if (is_file($thumb_water_pic)) {
            // 读取图片
            $image = Image::open($file);
            // 添加水印
            $watermark_pos   = $watermark_pos   == '' ? config('upload_thumb_water_position') : $watermark_pos;
            $watermark_alpha = $watermark_alpha == '' ? config('upload_thumb_water_alpha') : $watermark_alpha;
            $image->water($thumb_water_pic, $watermark_pos, $watermark_alpha);
            // 保存水印图片，覆盖原图
            $image->save($file);
        }
    }


    /**
     * 检查附件是否存在
     * @param string $md5 文件md5
     * @author 似水星辰 [ 2630481389@qq.com ]
     * @return \think\response\Json
     */
    public function check($md5 = '')
    {
        $md5 == '' && $this->error('参数错误');

        // 判断附件是否已存在
        if ($file_exists = UploadModel::get(['md5' => $md5])) {
            $file_path = $file_exists['path'];
            return json([
                'code'   => 1,
                'info'   => '上传成功',
                'class'  => 'success',
                'id'     => $file_exists['id'],
                'path'   => $file_path
            ]);
        } else {
            $this->error('文件不存在');
        }
    }
}