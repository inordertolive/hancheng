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
namespace service;
use think\Db;


/**
 * 数据库操作模型（备份还原导出）
 * Class Database
 * @package service
 */
class Database{
    /**
     * 文件指针
     * @var resource
     */
    private $fp;

    /**
     * 备份文件信息 part - 卷号，name - 文件名
     * @var array
     */
    private $file;

    /**
     * 当前打开文件大小
     * @var integer
     */
    private $size = 0;

    /**
     * 备份配置
     * @var integer
     */
    private $config;

    /**
     * 数据库备份构造方法
     * @param array  $file   备份或还原的文件信息
     * @param array  $config 备份配置信息
     * @param string $type   执行类型，export - 备份数据， import - 还原数据
     * @author 似水星辰 [ 2630481389@qq.com ]
     */
    public function __construct($file, $config, $type = 'export'){
        $this->file   = $file;
        $this->config = $config;
    }

    /**
     * 打开一个卷，用于写入数据
     * @param  integer $size 写入数据的大小
     * @author 似水星辰 [ 2630481389@qq.com ]
     */
    private function open($size){
        if($this->fp){
            $this->size += $size;
            if($this->size > $this->config['part']){
                $this->config['compress'] ? @gzclose($this->fp) : @fclose($this->fp);
                $this->fp = null;
                $this->file['part']++;
                session('backup_file', $this->file);
                $this->create();
            }
        } else {
            $backuppath = $this->config['path'];
            $filename   = "{$backuppath}{$this->file['name']}-{$this->file['part']}.sql";
            if($this->config['compress']){
                $filename = "{$filename}.gz";
                $this->fp = @gzopen($filename, "a{$this->config['level']}");
            } else {
                $this->fp = @fopen($filename, 'a');
            }
            $this->size = filesize($filename) + $size;
        }
    }

    /**
     * 写入初始数据
     * @return boolean true - 写入成功，false - 写入失败
     * @author 似水星辰 [ 2630481389@qq.com ]
     */
    public function create(){
        $sql  = "-- -----------------------------\n";
        $sql .= "-- SentCMS MySQL Data Transfer \n";
        $sql .= "-- \n";
        $sql .= "-- Host     : " . config('database.hostname') . "\n";
        $sql .= "-- Port     : " . config('database.hostport') . "\n";
        $sql .= "-- Database : " . config('database.database') . "\n";
        $sql .= "-- \n";
        $sql .= "-- Part : #{$this->file['part']}\n";
        $sql .= "-- Date : " . date("Y-m-d H:i:s") . "\n";
        $sql .= "-- -----------------------------\n\n";
        $sql .= "SET FOREIGN_KEY_CHECKS = 0;\n\n";
        return $this->write($sql);
    }

    /**
     * 写入SQL语句
     * @param  string $sql 要写入的SQL语句
     * @return boolean     true - 写入成功，false - 写入失败！
     * @author 似水星辰 [ 2630481389@qq.com ]
     */
    private function write($sql){
        $size = strlen($sql);
        
        //由于压缩原因，无法计算出压缩后的长度，这里假设压缩率为50%，
        //一般情况压缩率都会高于50%；
        $size = $this->config['compress'] ? $size / 2 : $size;
        
        $this->open($size); 
        return $this->config['compress'] ? @gzwrite($this->fp, $sql) : @fwrite($this->fp, $sql);
    }

    /**
     * 备份表结构
     * @param  string  $table 表名
     * @param  integer $start 起始行数
     * @return boolean        false - 备份失败
     * @author 似水星辰 [ 2630481389@qq.com ]
     */
    public function backup($table, $start){
        //创建DB对象
        $db = \think\Db::connect();

        //备份表结构
        if(0 == $start){
            $result = $db->query("SHOW CREATE TABLE `{$table}`");
            $sql  = "\n";
            $sql .= "-- -----------------------------\n";
            $sql .= "-- Table structure for `{$table}`\n";
            $sql .= "-- -----------------------------\n";
            $sql .= "DROP TABLE IF EXISTS `{$table}`;\n";
            $sql .= trim($result[0]['Create Table']) . ";\n\n";
            if(false === $this->write($sql)){
                return false;
            }
        }

        //数据总数
        $result = $db->query("SELECT COUNT(*) AS count FROM `{$table}`");
        $count  = $result['0']['count'];
            
        //备份表数据
        if($count){
            //写入数据注释
            if(0 == $start){
                $sql  = "-- -----------------------------\n";
                $sql .= "-- Records of `{$table}`\n";
                $sql .= "-- -----------------------------\n";
                $this->write($sql);
            }

            //备份数据记录
            $result = $db->query("SELECT * FROM `{$table}` LIMIT {$start}, 1000");
            foreach ($result as $row) {
                $row = array_map('addslashes', $row);
                $sql = "INSERT INTO `{$table}` VALUES ('" . str_replace(array("\r","\n"),array('\r','\n'),implode("', '", $row)) . "');\n";
                if(false === $this->write($sql)){
                    return false;
                }
            }

            //还有更多数据
            if($count > $start + 1000){
                return array($start + 1000, $count);
            }
        }

        //备份下一表
        return 0;
    }

    /**
     * 还原数据
     * @param $start
     * @return array|bool|int
     * @author 似水星辰 [ 2630481389@qq.com ]
     */
    public function import($start){
        //还原数据
        $db = \think\Db::connect();

        if($this->config['compress']){
            $gz   = gzopen($this->file[1], 'r');
            $size = 0;
        } else {
            $size = filesize($this->file[1]);
            $gz   = fopen($this->file[1], 'r');
        }
        
        $sql  = '';
        if($start){
            $this->config['compress'] ? gzseek($gz, $start) : fseek($gz, $start);
        }
        
        for($i = 0; $i < 1000; $i++){
            $sql .= $this->config['compress'] ? gzgets($gz) : fgets($gz); 
            if(preg_match('/.*;$/', trim($sql))){
                if(false !== $db->execute($sql)){
                    $start += strlen($sql);
                } else {
                    return false;
                }
                $sql = '';
            } elseif ($this->config['compress'] ? gzeof($gz) : feof($gz)) {
                return 0;
            }
        }

        return array($start, $size);
    }

	/**
     * 导出
     * @param array $tables 表名
     * @param string $path 导出路径
     * @param string $prefix 表前缀
     * @param integer $export_data 是否导出数据
     * @author 似水星辰 [ 2630481389@qq.com ]
     * @return bool
     */
    public static function export($tables = [], $path = '', $prefix = '', $export_data = 1){
        $tables = is_array($tables) ? $tables : explode(',', $tables);
        $datetime = date('Y-m-d H:i:s', time());
        $sql  = "-- -----------------------------\n";
        $sql .= "-- 导出时间 `{$datetime}`\n";
        $sql .= "-- -----------------------------\n";

        if (!empty($tables)) {
            foreach ($tables as $table) {
                $sql .= self::getSql($prefix.$table, $export_data);
            }

            // 写入文件
            if (file_put_contents($path, $sql)) {
                return true;
            };
        }
        return false;
    }

    /**
     * 导出卸载文件
     * @param  array $tables 表名
     * @param  string $path 导出路径
     * @param string $prefix 表前缀
     * @author 似水星辰 [ 2630481389@qq.com ]
     * @return bool
     */
    public static function exportUninstall($tables = [], $path = '', $prefix = ''){
        $tables = is_array($tables) ? $tables : explode(',', $tables);
        $datetime = date('Y-m-d H:i:s', time());
        $sql  = "-- -----------------------------\n";
        $sql .= "-- 导出时间 `{$datetime}`\n";
        $sql .= "-- -----------------------------\n";

        if (!empty($tables)) {
            foreach ($tables as $table) {
                $sql .= "DROP TABLE IF EXISTS `{$prefix}{$table}`;\n";
            }

            // 写入文件
            if (file_put_contents($path, $sql)) {
                return true;
            };
        }
        return false;
    }

	/**
     * 获取表结构和数据
     * @param string  $table 表名
     * @param integer $export_data 是否导出数据
     * @param integer $start 起始行数
     * @author 似水星辰 [ 2630481389@qq.com ]
     * @return string
     */
    private static function getSql($table = '', $export_data = 0, $start = 0)
    {
        $sql  = "";
        if (Db::query("SHOW TABLES LIKE '%{$table}%'")) {
            // 表结构
            if ($start == 0) {
                $result = Db::query("SHOW CREATE TABLE `{$table}`");
                $sql .= "\n-- -----------------------------\n";
                $sql .= "-- 表结构 `{$table}`\n";
                $sql .= "-- -----------------------------\n";
                $sql .= "DROP TABLE IF EXISTS `{$table}`;\n";
                $sql .= trim($result[0]['Create Table']) . ";\n\n";
            }

            // 表数据
            if ($export_data) {
                $sql .= "-- -----------------------------\n";
                $sql .= "-- 表数据 `{$table}`\n";
                $sql .= "-- -----------------------------\n";

                // 数据总数
                $result = Db::query("SELECT COUNT(*) AS count FROM `{$table}`");
                $count  = $result['0']['count'];

                // 备份数据记录
                $result = Db::query("SELECT * FROM `{$table}` LIMIT {$start}, 1000");
                foreach ($result as $row) {
                    $row = array_map('addslashes', $row);
                    $sql .= "INSERT INTO `{$table}` VALUES ('" . str_replace(array("\r","\n"),array('\r','\n'),implode("', '", $row)) . "');\n";
                }

                // 还有更多数据
                if($count > $start + 1000){
                    $sql .= self::getSql($table, $export_data, $start + 1000);
                }
            }
        }

        return $sql;
    }

    /**
     * 析构方法，用于关闭文件资源
     */
    public function __destruct(){
        $this->config['compress'] ? @gzclose($this->fp) : @fclose($this->fp);
    }
}