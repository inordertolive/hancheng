<?php

class WeixinConfig {

    public static $config = null;

    public static function getConfig($name) {

        if (isset(self::$config[$name])) {
            return self::$config[$name];
        }
        throw new \Exception("缺少" . $name . "参数");
    }

}
