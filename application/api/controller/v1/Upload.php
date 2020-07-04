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
namespace app\api\controller\v1;

use app\api\controller\Base;
use service\ApiReturn;
use app\admin\model\Upload as UploadMore;

class Upload extends Base
{
    /**
     * 上传图片
     * @param $data
     * @param array $user
     * @return \think\response\Json
     * @since 2019/4/20 11:30
     * @author 似水星辰 [ 2630481389@qq.com ]
     */
    public function upload_img($data, $user = [])
    {
        $dir = $data['dir'] ? $data['dir'] : 'images';  // 保存的目录:images
        $module = $data['module'] ? $data['module'] : 'user';
        $userid = isset($user['id']) ? $user['id'] : 0;

        $files = request()->file('file');
        if (!isset($files)) {
            return ApiReturn::r(0, '未获取上传文件，请检查是否开启相册权限');
        }
        foreach ($files as $file) {
            // 移动到框架应用根目录/uploads/ 目录下
            $info = $file->validate(['ext' => 'jpg,png,gif'])->move(ROOT_PATH . config('upload_path') . $dir);
            if ($info) {
                $filename = $info->getFilename();
                //创建缩略图
                $thumb_file_path = ROOT_PATH . config('upload_path') . $dir . '/' . date('Ymd') . '/thumb';
                if (!is_dir($thumb_file_path)) {
                    mkdir($thumb_file_path, 0777, true);
                }
                $image = \think\Image::open(ROOT_PATH . config('upload_path') . $dir . '/' . date('Ymd') . '/' . $filename);
                $image->thumb(400, 400)->save($thumb_file_path . '/' . $filename);

                // 获取附件信息
                $file_info = [
                    'uid' => $userid ? $userid : 0,
                    'name' => $filename,
                    'mime' => $info->getMime(),
                    'path' => config('web_site_domain') . '/uploads/' . $dir . '/' . date('Ymd') . '/' . $filename,
                    'ext' => $info->getExtension(),
                    'size' => $info->getSize(),
                    'md5' => $info->hash('md5'),
                    'sha1' => $info->hash('sha1'),
                    'thumb' => config('web_site_domain') . '/uploads/' . $dir . '/' . date('Ymd') . '/thumb/' . $filename,
                    'module' => $module,
                ];

                // 写入数据库
                if ($file_add = UploadMore::create($file_info)) {
                    $file_path = $file_info['path'];
                    $str[] = [
                        'id' => $file_add['id'],
                        'path' => $file_path,
                        'thumb' => $file_info['thumb']
                    ];
                }
            } else {
                return ApiReturn::r(0, [], $file->getError());
            }

            return ApiReturn::r(1, $str, "上传成功");
        }
    }

}