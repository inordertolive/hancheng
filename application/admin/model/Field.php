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
 * 字段模型
 * @package app\admin\model
 */
class Field extends ThinkModel
{
    // 设置当前模型对应的完整数据表名称
    protected $table = '__ADMIN_MODEL_FIELD__';

    // 自动写入时间戳
    protected $autoWriteTimestamp = true;

    // 当前表名
    protected $_table_name = '';

    /**
     * 创建字段
     * @param null $field 字段数据
     * @param int $type 类型，0：自定义表，1：cms模型
     * @return bool
     * @author 似水星辰 [ 2630481389@qq.com ]
     */
    public function newField($field = null, $type = 0)
    {
        if ($field === null) {
            $this->error = '缺少参数';
            return false;
        }

        if ($this->tableExist($field['model'], $type)) {
            if($field['define'] == 'text NOT NULL'){
                $sql = <<<EOF
            ALTER TABLE `{$this->_table_name}`
            ADD COLUMN `{$field['name']}` {$field['define']} COMMENT '{$field['title']}';
EOF;
            }else{
                $sql = <<<EOF
            ALTER TABLE `{$this->_table_name}`
            ADD COLUMN `{$field['name']}` {$field['define']} DEFAULT '{$field['value']}' COMMENT '{$field['title']}';
EOF;
            }

        } else {
            $mdoel_title = get_model_title($field['model']);

            // 新建普通扩展表
            $sql = <<<EOF
                CREATE TABLE IF NOT EXISTS `{$this->_table_name}` (
                `aid` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '文档id' ,
                `{$field['name']}` {$field['define']} COMMENT '{$field['title']}' ,
                PRIMARY KEY (`aid`)
                )
                ENGINE=InnoDB
                DEFAULT CHARACTER SET=utf8 COLLATE=utf8_general_ci
                CHECKSUM=0
                ROW_FORMAT=DYNAMIC
                DELAY_KEY_WRITE=0
                COMMENT='{$mdoel_title}模型扩展表'
                ;
EOF;
        }

        try {
            Db::execute($sql);
        } catch(\Exception $e) {
            $this->error = $sql.'字段添加失败'.$e->getMessage();
            return false;
        }

        return true;
    }

    /**
     * 更新字段
     * @param null $field 字段数据
     * @author 似水星辰 [ 2630481389@qq.com ]
     * @return bool
     */
    public function updateField($field = null, $type = 0)
    {
        if ($field === null) {
            return false;
        }

        // 获取原字段名
        $field_name = $this->where('id', $field['id'])->value('name');

        if ($this->tableExist($field['model'], $type)) {
			if($field['value'] != ''){
				$sql = <<<EOF
				ALTER TABLE `{$this->_table_name}`
				CHANGE COLUMN `{$field_name}` `{$field['name']}` {$field['define']} DEFAULT '{$field['value']}' COMMENT '{$field['title']}';
EOF;
			}else{
				$sql = <<<EOF
				ALTER TABLE `{$this->_table_name}`
				CHANGE COLUMN `{$field_name}` `{$field['name']}` {$field['define']} COMMENT '{$field['title']}';
EOF;
			}
            try {
                Db::execute($sql);
            } catch(\Exception $e) {
                return false;
            }
            return true;
        } else {
            return false;
        }
    }

    /**
     * 删除字段
     * @param null $field 字段数据
     * @author 似水星辰 [ 2630481389@qq.com ]
     * @return bool
     */
    public function deleteField($field = null, $type = 0)
    {
        if ($field === null) {
            return false;
        }

        if ($this->tableExist($field['model'] , $type)) {
            $sql = <<<EOF
            ALTER TABLE `{$this->_table_name}`
            DROP COLUMN `{$field['name']}`;
EOF;
            try {
                Db::execute($sql);
            } catch(\Exception $e) {
                return false;
            }
            return true;
        } else {
            return false;
        }
    }

    /**
     * 检查表是否存在
     * @param string $model 文档模型id
     * @author 似水星辰 [ 2630481389@qq.com ]
     * @return bool
     */
    private function tableExist($model = '', $type = 0)
    {
        if($type == 0){
            $this->_table_name = strtolower(get_model_table($model));
        }else if($type == 1){
            $this->_table_name = strtolower(get_cms_table($model));
        }

        return true == Db::query("SHOW TABLES LIKE '{$this->_table_name}'");
    }
	
	/**
     * 获取制定模型的字段列表
     * @param string $id 模型id
     * @author 似水星辰 [ 2630481389@qq.com ]
     * @return bool
     */
	public function getFieldlist($id){
		$data_list = SELF::where(['status'=>1,'show'=>1,'model'=>$id])->order('sort asc,id asc')->column('id,name,title,type,extra');
		foreach($data_list as $k=>$v){
			unset($data_list[$k]['id']);
			if($v['type']=='radio'){
				$data_list[$k]['extra'] = parse_attr($v['extra']);
			}
		}
		return $data_list;
	}
}