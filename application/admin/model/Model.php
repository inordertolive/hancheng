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

use think\Model as ThinkModel;
use think\Db;

/**
 * 自定义表
 * @package app\cms\model
 */
class Model extends ThinkModel
{
    // 设置当前模型对应的完整数据表名称
    protected $table = '__ADMIN_MODEL__';

    // 自动写入时间戳
    protected $autoWriteTimestamp = true;

    /**
     * 获取自定义表列表
     * @author 似水星辰 [ 2630481389@qq.com ]
     * @return array|mixed
     */
    public static function getList()
    {
        $data_list = cache('admin_model_list');
        if (!$data_list) {
            $data_list = self::where('status', 1)->column(true, 'id');
            // 非开发模式，缓存数据
            if (config('develop_mode') == 0) {
                cache('admin_model_list', $data_list);
            }
        }
        return $data_list;
    }

    /**
     * 获取自定义表标题列表（只含id和title）
     * @param array $map 筛选条件
     * @author 似水星辰 [ 2630481389@qq.com ]
     * @return array|mixed
     */
    public static function getTitleList($map = [])
    {
        return self::where('status', 1)->where($map)->column('id,title');
    }

    /**
     * 删除附加表
     * @param null $model 自定义表id
     * @author 似水星辰 [ 2630481389@qq.com ]
     * @return bool
     */
    public static function deleteTable($model = null)
    {
        if ($model === null) {
            return false;
        }

        $table_name = self::where('id', $model)->value('table');
        return false !== Db::execute("DROP TABLE IF EXISTS `{$table_name}`");
    }

    /**
     * 创建独立模型表
     * @param mixed $data 模型数据
     * @author 似水星辰 [ 2630481389@qq.com ]
     * @return bool
     */
    public static function createTable($data)
    {
        if ($data['type'] == 2) {
            // 新建商城扩展表
            $sql = <<<EOF
            CREATE TABLE IF NOT EXISTS `{$data['table']}` (
            `aid` int(11) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'aid' ,
            `uid` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '用户id' ,
            `create_time` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '创建时间' ,
            `update_time` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '更新时间' ,
            PRIMARY KEY (`aid`)
            )
            ENGINE=InnoDB
            DEFAULT CHARACTER SET=utf8 COLLATE=utf8_general_ci
            CHECKSUM=0
            ROW_FORMAT=DYNAMIC
            DELAY_KEY_WRITE=0
            COMMENT='{$data['title']}'
            ;
EOF;
        }else if ($data['type'] == 1) {
            // 新建文章扩展表
            $sql = <<<EOF
            CREATE TABLE IF NOT EXISTS `{$data['table']}` (
            `aid` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '主表id' ,
            PRIMARY KEY (`aid`)
            )
            ENGINE=InnoDB
            DEFAULT CHARACTER SET=utf8 COLLATE=utf8_general_ci
            CHECKSUM=0
            ROW_FORMAT=DYNAMIC
            DELAY_KEY_WRITE=0
            COMMENT='{$data['title']}'
            ;
EOF;
        } else {
            // 新建空表
            $sql = <<<EOF
                CREATE TABLE IF NOT EXISTS `{$data['table']}` (
				`aid` int(11) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'aid' ,
				`create_time` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '创建时间' ,
				`update_time` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '更新时间' ,
				`status` int(1) UNSIGNED NOT NULL DEFAULT 1 COMMENT '状态' ,
                PRIMARY KEY (`aid`)
                )
                ENGINE=InnoDB
                DEFAULT CHARACTER SET=utf8 COLLATE=utf8_general_ci
                CHECKSUM=0
                ROW_FORMAT=DYNAMIC
                DELAY_KEY_WRITE=0
                COMMENT='{$data['title']}'
                ;
EOF;
        }

        try {
            Db::execute($sql);
        } catch(\Exception $e) {
            return false;
        }
		// 添加默认字段
        $default = [
            'model'       => $data['id'],
            'create_time' => request()->time(),
            'update_time' => request()->time(),
            'status'      => 1
        ];
        if ($data['type'] == 3 || $data['type'] == 2) {
            $data = [
				[
                    'name'        => 'aid',
                    'title'       => 'ID',
                    'define'      => 'int(11) UNSIGNED NOT NULL',
                    'type'        => 'number',
                    'show'        => 0,
					'value'       => 0,
                ],
                [
                    'name'        => 'uid',
                    'title'       => '用户id',
                    'define'      => 'int(11) UNSIGNED NOT NULL',
                    'type'        => 'number',
                    'show'        => 0,
                    'value'       => 0,
                ]
            ];
        }else if ($data['type'] == 1) {
            $data = [
				[
                    'name'        => 'aid',
                    'title'       => '文章主表id',
                    'define'      => 'int(11) UNSIGNED NOT NULL',
                    'type'        => 'number',
                    'show'        => 0,
					'value'       => 0,
                ]
            ];
        }else if ($data['type'] == 0) {
            $data = [
				[
                    'name'        => 'aid',
                    'title'       => 'ID',
                    'define'      => 'int(11) UNSIGNED NOT NULL',
                    'type'        => 'number',
                    'show'        => 0,
					'value'       => 0,
                ],
				/*[
                    'name'        => 'title',
                    'title'       => '标题',
                    'define'      => 'varchar(256) NOT NULL',
                    'type'        => 'text',
                    'show'        => 1
                ],
				[
                    'name'        => 'sort',
                    'title'       => '排序',
                    'define'      => 'int(11) UNSIGNED NOT NULL',
                    'type'        => 'text',
                    'show'        => 1,
                    'value'       => 100,
                ],
				[
                    'name'        => 'status',
                    'title'       => '状态',
                    'define'      => 'int(1) UNSIGNED NOT NULL',
                    'type'        => 'text',
                    'show'        => 0,
                    'value'       => 0
                ]*/
            ];
        }
		foreach ($data as $item) {
            $item = array_merge($item, $default);
            Db::name('admin_model_field')->insert($item);
        }
        return true;
    }
}