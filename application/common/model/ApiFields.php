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

namespace app\common\model;
use think\Model;

/**
 * 接口字段模型
 * 用于保存各个API的字段规则
 * @package app\common\model
 * @author 似水星辰 [ 2630481389@qq.com ]
 */
class ApiFields extends Model {

    protected $table = '__ADMIN_API_FIELDS__';
    //只读字段,一旦写入，就无法更改。
    protected $readonly = ['hash'];
    // 新增自动完成列表
    protected $insert = [];
    //更新自动完成列表
    protected $update = [];
    //新增和更新自动完成列表
    protected $auto = [];
    //字段类型
    public $dataType = array(
        1 => 'Integer[整数]',
        2 => 'String[字符串]',
        3 => 'Boolean[布尔]',
        4 => 'Enum[枚举]',
        5 => 'Float[浮点数]',
        6 => 'File[文件]',
        7 => 'Mobile[手机号]',
        8 => 'Object[对象]',
        9 => 'Array[数组]',
        10 => 'Email[邮箱]',
        11 => 'Date[日期]',
        12 => 'Url',
        13 => 'IP',
    );

    //关联模型
    public function apiList() {
        return $this->hasOne('ApiList', 'hash', 'hash');
    }

    /**
     * 是否必填获取器
     * @param $value
     * @param $data
     * @return mixed
     * @author 似水星辰 [ 2630481389@qq.com ]
     */
    public function getIsMustTurnAttr($value, $data) { //是否必须 isMust 字段 [获取器]
        $turnArr = [0 => '选填', 1 => '必填'];
        return $turnArr[$data['isMust']];
    }

    /**
     * 类型获取器
     * @param $value
     * @param $data
     * @return mixed
     * @author 似水星辰 [ 2630481389@qq.com ]
     */
    public function getDataTypeTurnAttr($value, $data) { //字段类型 dataType 字段 [获取器]
        $turnArr = [
            1 => 'Integer[整数]',
            2 => 'String[字符串]',
            3 => 'Boolean[布尔]',
            4 => 'Enum[枚举]',
            5 => 'Float[浮点数]',
            6 => 'File[文件]',
            7 => 'Mobile[手机号]',
            8 => 'Object[对象]',
            9 => 'Array[数组]',
            10 => 'Email[邮箱]',
            11 => 'Date[日期]',
            12 => 'Url',
            13 => 'IP',
        ];
        return $turnArr[$data['dataType']];
    }    
   
    /**
     * 获取缓存字段
     * @author 似水星辰 [ 2630481389@qq.com ]
     * @param string $hash 接口HASH
     * @return array|null
     */
    
    public static function getCacheFields($hash,$type = 1){
        $key = "apiFields_" .$hash .  '_'.$type;      
        $fields = cache($key);
        if(!$fields){    
            $map = ['hash' => $hash];
            $map['type'] = $type; 
            $fields = self::all($map); //获取数据库的 请求字段
            cache($key, $fields, 7200); //接口信息          
        }
        return  $fields;
    }
    //获取缓存规则
    public static function cacheBuildValidateRule($rule){
      
        $key = "apiRule_" . md5(json_encode($rule));
        $newRule = cache( $key);
        if(!$newRule){    
            $newRule = self::buildValidateRule($rule);
            cache( $key, $newRule, 7200); //接口信息     
        }
        return  $newRule;
    }
    
    /**
     * 将数据库中的规则转换成TP_Validate使用的规则数组
     * @param array $rule
     * @author 似水星辰 [ 2630481389@qq.com ]
     * @return array
     */
    public static function buildValidateRule($rule = array()) {
        $newRule = [];
        if ($rule) {
            foreach ($rule as $value) {
                if ($value['isMust'] && $value['default'] == '') {
                    $newRule[$value['fieldName']][] = 'require'; //必填
                }
                switch ($value['dataType']) {
                    case 1: //Integer[整数]
                        $newRule[$value['fieldName']][] = 'number';
                        if ($value['range']) {
                            $range = htmlspecialchars_decode($value['range']);
                            $range = json_decode($range, true);
                            if (isset($range['min'])) {
                                $newRule[$value['fieldName']]['egt'] = $range['min']; // >= 大于等于
                            }
                            if (isset($range['max'])) {
                                $newRule[$value['fieldName']]['elt'] = $range['max']; // <= 小于等于
                            }
                        }
                        break;
                    case 2: //String[字符串]
                        if ($value['range']) {
                            $range = htmlspecialchars_decode($value['range']);
                            $range = json_decode($range, true);
                            if (isset($range['min'])) {
                                $newRule[$value['fieldName']]['min'] = $range['min']; //最小长度
                            }
                            if (isset($range['max'])) {
                                $newRule[$value['fieldName']]['max'] = $range['max']; //最大长度
                            }
                        }
                        break;
                    case 3: //Boolean[布尔]
                        $newRule[$value['fieldName']][] = 'boolean';
                        break;
                    case 4: //Enum[枚举]
                        if ($value['range']) {
                            $range = htmlspecialchars_decode($value['range']);
                            $range = json_decode($range, true);
                            $newRule[$value['fieldName']]['in'] = implode(',', $range);
                        }
                        break;
                    case 5: //Float[浮点数]
                        $newRule[$value['fieldName']][] = 'float';
                        if ($value['range']) {
                            $range = htmlspecialchars_decode($value['range']);
                            $range = json_decode($range, true);
                            if (isset($range['min'])) {
                                $newRule[$value['fieldName']]['egt'] = $range['min']; // >= 大于等于
                            }
                            if (isset($range['max'])) {
                                $newRule[$value['fieldName']]['elt'] = $range['max']; // <= 小于等于
                            }
                        }
                        break;
                    case 6: //File[文件]
                        $newRule[$value['fieldName']][] = 'file';
                        break;
                    case 7: //Mobile[手机号]
                        $newRule[$value['fieldName']][] = 'mobile';
                        break;
                    case 9: //Array[数组]
                        $newRule[$value['fieldName']][] = 'array';
                        if ($value['range']) {
                            $range = htmlspecialchars_decode($value['range']);
                            $range = json_decode($range, true);
                            if (isset($range['min'])) {
                                $newRule[$value['fieldName']]['min'] = $range['min']; //最小长度
                            }
                            if (isset($range['max'])) {
                                $newRule[$value['fieldName']]['max'] = $range['max']; //最大长度
                            }
                        }
                        break;
                    case 10: //Email[邮箱]
                        $newRule[$value['fieldName']][] = 'email';
                        break;
                    case 11: //Date[日期]
                        $newRule[$value['fieldName']][] = 'date';
                        break;
                    case 12: //Url
                        $newRule[$value['fieldName']][] = 'url';
                        break;
                    case 13: //IP
                        $newRule[$value['fieldName']][] = 'ip';
                        break;
                    default:
                        $newRule[$value['fieldName']][] = '';
                }
            }
        }
        return $newRule;
    }

}
